<?php

namespace Oro\Bundle\SearchBundle\Provider;

use Oro\Bundle\SearchBundle\Engine\Indexer;
use Oro\Bundle\SearchBundle\Query\Result\Item;

class ResultProvider
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
     * Returns grouped search results
     *
     * @param string $string
     * @return array
     */
    public function getGroupedResults($string)
    {
        $result = $this->indexer->simpleSearch($string);

        // empty key array contains all data
        $groupedResults = array(
            '' => array(
                'count'  => 0,
                'config' => array()
            )
        );

        /** @var $item Item */
        foreach ($result->getElements() as $item) {
            $entityConfig = $item->getEntityConfig();
            $alias = $entityConfig['alias'];
            if (!isset($groupedResults[$alias])) {
                $groupedResults[$alias] = array(
                    'count'  => 0,
                    'config' => $entityConfig,
                );
            }

            $groupedResults[$alias]['count']++;
            $groupedResults['']['count']++;
        }

        return $groupedResults;
    }
}
