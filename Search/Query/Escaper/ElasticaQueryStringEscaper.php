<?php

/*
 * This file is part of the phlexible frontend search package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\FrontendSearchBundle\Search\Query\Escaper;

use Elastica\Util;

/**
 * Elastica based query string escaper.
 *
 * @author Tim Hoepfner <thoepfner@brainbits.net>
 * @author Stephan Wentz <swentz@brainbits.net>
 */
class ElasticaQueryStringEscaper implements QueryStringEscaperInterface
{
    /**
     * {@inheritdoc}
     */
    public function escapeQueryString($queryString)
    {
        return Util::escapeTerm($queryString);
    }
}
