<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\FrontendSearchBundle\Search\Query;

use Elastica\Query;

/**
 * Multi match query builder
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class MultiMatchQueryBuilder implements QueryBuilderInterface
{
    /**
     * @param string $queryString
     * @param array  $fields
     *
     * @return Query|Query\Bool
     */
    public function build($queryString, array $fields)
    {
        $queryString = str_replace('/', '\/', $queryString);
        $queryString = str_replace(':', '\:', $queryString);

        $boostedFields = array();
        foreach ($fields as $field => $boost) {
            $boostedFields[] = "$field^$boost";
        }
        $query = new Query\MultiMatch();
        $query->setFields($boostedFields);
        $query->setQuery($queryString);

        return $query;
    }
}