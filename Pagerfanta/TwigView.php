<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\FrontendSearchBundle\Pagerfanta;

use Pagerfanta\PagerfantaInterface;
use Pagerfanta\View\ViewInterface;

/**
 * @author Pablo Díez <pablodip@gmail.com>
 */
class TwigView implements ViewInterface
{
    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var PagerfantaInterface
     */
    private $pagerfanta;

    /**
     * @var int
     */
    private $proximity;

    /**
     * @var string
     */
    private $routeName;

    /**
     * @var string
     */
    private $ajaxRouteName;

    /**
     * @var array
     */
    private $parameters = array();

    /**
     * @var int
     */
    private $currentPage;

    /**
     * @var int
     */
    private $nbPages;

    /**
     * @var int
     */
    private $startPage;

    /**
     * @var int
     */
    private $endPage;

    /**
     * @param \Twig_Environment $twig
     */
    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * {@inheritdoc}
     */
    public function render(PagerfantaInterface $pagerfanta, $routeGenerator, array $options = array())
    {
        $this->pagerfanta = $pagerfanta;
        $this->currentPage = $pagerfanta->getCurrentPage();
        $this->nbPages = $pagerfanta->getNbPages();

        $this->proximity = isset($options['proximity']) ? (int) $options['proximity'] : $this->getDefaultProximity();
        $this->routeName = $options['routeName'];
        $this->ajaxRouteName = $options['ajaxRouteName'];
        $this->parameters = isset($options['parameters']) ? $options['parameters'] : array();

        $this->calculateStartAndEndPage();

        return $this->twig->render('::search/pager.html.twig', array(
            'view' => $this
        ));
    }

    /**
     * @return mixed
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * @return mixed
     */
    public function getAjaxRouteName()
    {
        return $this->ajaxRouteName;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;

    }

    /**
     * Returns whether there is prvious page or not.
     *
     * @return Boolean
     */
    public function hasPreviousPage()
    {
        return $this->pagerfanta->hasPreviousPage();
    }

    /**
     * Returns the previous page.
     *
     * @return integer
     */
    public function getPreviousPage()
    {
        return $this->pagerfanta->getPreviousPage();
    }

    /**
     * Returns whether there is next page or not.
     *
     * @return Boolean
     */
    public function hasNextPage()
    {
        return $this->pagerfanta->hasNextPage();
    }

    /**
     * Returns the next page.
     *
     * @return integer
     */
    public function getNextPage()
    {
        return $this->pagerfanta->getNextPage();
    }

    /**
     * Returns the current page.
     *
     * @return integer
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * Returns the number of pages.
     *
     * @return integer
     */
    public function getNbPages()
    {
        return $this->nbPages;
    }

    /**
     * Returns the start page
     *
     * @return integer
     */
    public function getStartPage()
    {
        return $this->startPage;
    }

    /**
     * Returns the end page
     *
     * @return integer
     */
    public function getEndPage()
    {
        return $this->endPage;
    }

    /**
     * Returns the page with negative offset from the last page
     *
     * @param int $page
     *
     * @return integer
     */
    public function toLast($page)
    {
        return $this->pagerfanta->getNbPages() - ($page - 1);
    }

    /**
     * @return int
     */
    private function getDefaultProximity()
    {
        return 2;
    }

    private function calculateStartAndEndPage()
    {
        $startPage = $this->currentPage - $this->proximity;
        $endPage = $this->currentPage + $this->proximity;

        if ($this->startPageUnderflow($startPage)) {
            $endPage = $this->calculateEndPageForStartPageUnderflow($startPage, $endPage);
            $startPage = 1;
        }
        if ($this->endPageOverflow($endPage)) {
            $startPage = $this->calculateStartPageForEndPageOverflow($startPage, $endPage);
            $endPage = $this->nbPages;
        }

        $this->startPage = $startPage;
        $this->endPage = $endPage;
    }

    private function startPageUnderflow($startPage)
    {
        return $startPage < 1;
    }

    private function endPageOverflow($endPage)
    {
        return $endPage > $this->nbPages;
    }

    private function calculateEndPageForStartPageUnderflow($startPage, $endPage)
    {
        return min($endPage + (1 - $startPage), $this->nbPages);
    }

    private function calculateStartPageForEndPageOverflow($startPage, $endPage)
    {
        return max($startPage - ($endPage - $this->nbPages), 1);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'twig';
    }
}

/*

CSS:

.pagerfanta {
}

.pagerfanta a,
.pagerfanta span {
    display: inline-block;
    border: 1px solid blue;
    color: blue;
    margin-right: .2em;
    padding: .25em .35em;
}

.pagerfanta a {
    text-decoration: none;
}

.pagerfanta a:hover {
    background: #ccf;
}

.pagerfanta .dots {
    border-width: 0;
}

.pagerfanta .current {
    background: #ccf;
    font-weight: bold;
}

.pagerfanta .disabled {
    border-color: #ccf;
    color: #ccf;
}

COLORS:

.pagerfanta a,
.pagerfanta span {
    border-color: blue;
    color: blue;
}

.pagerfanta a:hover {
    background: #ccf;
}

.pagerfanta .current {
    background: #ccf;
}

.pagerfanta .disabled {
    border-color: #ccf;
    color: #cf;
}

*/