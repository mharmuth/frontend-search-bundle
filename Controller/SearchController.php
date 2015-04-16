<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\FrontendSearchBundle\Controller;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\NullAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Search controller
 *
 * @author Stephan Wentz <sw@brainbits.net>
 * @Route("/{_locale}/_search")
 */
class SearchController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response
     * @Route("/query", name="frontendsearch_query")
     */
    public function queryAction(Request $request)
    {
        $queryString = trim($request->get('term', ''));
        $siterootId = $request->get('siterootId');
        $limit = (int) $request->get('limit', 10);
        $page = (int) $request->get('page', 1);

        if (strlen($queryString) == 0) {
            return new Response('');
        }

        if (!mb_check_encoding($queryString, 'UTF-8')) {
            return new Response('');
        }

        $elementSearch = $this->get('phlexible_frontend_search.element_search');

        $start = ($page - 1) * $limit;

        $result = $elementSearch->search($queryString, $request->getLocale(), $siterootId, $limit, $start);

        $suggestions = array();
        if (!$result->getTotalHits()) {
            $suggestions = $elementSearch->suggest($queryString, $request->getLocale(), $siterootId);
        }

        $template = $this->container->getParameter('phlexible_frontend_search.results.template');

        $adapter = new NullAdapter($result->getTotalHits());
        $pagerfanta = new Pagerfanta($adapter);

        $pagerfanta
            ->setMaxPerPage($limit)
            ->setCurrentPage($page); // 1 by default

        return $this->render(
            $template,
            array(
                'term'        => $queryString,
                'siterootId'  => $siterootId,
                'limit'       => $limit,
                'start'       => $start,
                'page'        => $page,
                'total'       => $result->getTotalHits(),
                'hasMore'     => $result->getTotalHits() > $limit + $start,
                'result'      => $result,
                'suggestions' => $suggestions,
                'pager'       => $pagerfanta
            )
        );
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @Route("/query_json", name="frontendsearch_query_json")
     */
    public function queryJsonAction(Request $request)
    {
        $queryString = strtolower(trim($request->get('term')));
        $siterootId = $request->get('siterootId');
        $limit = (int) $request->get('limit', 10);
        $page = (int) $request->get('page', 1);

        if (strlen($queryString) == 0) {
            return new JsonResponse(array());
        }

        if (!mb_check_encoding($queryString, 'UTF-8')) {
            return new JsonResponse(array());
        }

        $elementSearch = $this->get('phlexible_frontend_search.element_search');

        $start = ($page - 1) * $limit;

        $result = $elementSearch->search($queryString, $request->getLocale(), $siterootId, $limit, $start);

        $suggestions = array();
        if (!$result->getTotalHits()) {
            $suggestions = $elementSearch->suggest($queryString, $request->getLocale(), $siterootId);
        }

        $template = $this->container->getParameter('phlexible_frontend_search.results.template');

        $adapter = new ArrayAdapter($result->getResults());
        $pagerfanta = new Pagerfanta($adapter);

        $pagerfanta
            ->setMaxPerPage($limit)
            ->setCurrentPage($page); // 1 by default

        $view = $this->renderView(
            $template,
            array(
                'term'        => $queryString,
                'siterootId'  => $siterootId,
                'limit'       => $limit,
                'start'       => $start,
                'page'        => $page,
                'total'       => $result->getTotalHits(),
                'hasMore'     => $result->getTotalHits() > $limit + $start,
                'result'      => $result,
                'suggestions' => $suggestions,
                'pager'       => $pagerfanta
            )
        );

        return new JsonResponse(
            array(
                'start'       => $start,
                'limit'       => $limit,
                'total'       => $result->getTotalHits(),
                'result'      => $result,
                'suggestions' => $suggestions,
                'view'        => $view
            )
        );
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @Route("/suggest", name="frontendsearch_suggest")
     */
    public function suggestAction(Request $request)
    {
        /*
{
  "suggest": {
    "didYouMean": {
      "text": "schmrz",
      "phrase": {
        "field": "did_you_mean"
      }
    }
  },
  "query": {
    "multi_match": {
      "query": "schmrz",
      "fields": [
        "content",
        "title"
      ]
    }
  }
}
         */
        $siterootId = $request->get('siterootId');
        $queryString = strtolower(trim($request->get('term')));

        $elementSearch = $this->get('phlexible_frontend_search.element_search');

        $suggestions = $elementSearch->suggest($queryString, $request->getLocale(), $siterootId);

        return new JsonResponse($suggestions);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @Route("/complete", name="frontendsearch_complete")
     */
    public function completeAction(Request $request)
    {
        /*
{
  "aggs": {
    "autocomplete": {
      "terms": {
        "field": "autocomplete",
        "order": {
          "_count": "desc"
        },
        "include": {
          "pattern": "lor.*"
        }
      }
    }
  },
  "query": {
    "prefix": {
      "autocomplete": {
        "value": "lor"
      }
    }
  }
}
         */

        $siterootId = $request->get('siterootId');
        $queryString = strtolower(trim($request->get('term')));

        $elementSearch = $this->get('phlexible_frontend_search.element_search');

        $autocompletes = $elementSearch->autocomplete($queryString, $request->getLocale(), $siterootId);

        return new JsonResponse($autocompletes);
    }
}
