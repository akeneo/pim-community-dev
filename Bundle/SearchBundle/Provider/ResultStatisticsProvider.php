<?php

namespace Oro\Bundle\SearchBundle\Provider;

use Oro\Bundle\SearchBundle\Engine\Indexer;
use Oro\Bundle\SearchBundle\Query\Result\Item;

class ResultStatisticsProvider
{
    /**
     * @var Indexer
     */
    protected $indexer;

    /**
     * @param Indexer $indexer
     */
    public function __construct(Indexer $indexer)
    {
        $this->indexer = $indexer;
    }

    /**
     *
     * @param $query
     * @return \Oro\Bundle\SearchBundle\Query\Result
     */
    public function getResults($query)
    {
        return $this->indexer->simpleSearch($query);
    }

    /**
     * Returns grouped search results
     *
     * @param string $string
     * @return array
     */
    public function getGroupedResults($string)
    {
        $search = $this->getResults($string);

        // empty key array contains all data
        $result = array(
            '' => array(
                'count'  => 0,
                'class'  => '',
                'config' => array()
            )
        );

        /** @var $item Item */
        foreach ($search->getElements() as $item) {
            $config = $item->getEntityConfig();
            $alias  = $config['alias'];

            if (!isset($result[$alias])) {
                $result[$alias] = array(
                    'count'  => 0,
                    'class'  => $item->getEntityName(),
                    'config' => $config,
                );
            }

            $result[$alias]['count']++;
            $result['']['count']++;
        }

        return $result;
    }
}
