<?php
declare(strict_types=1);

namespace Akeneo\Tool\Bundle\DatabaseMetadataBundle\Query;

use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Factory\IndexResultsFactory;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Model\EntityIndexConfiguration;

use Traversable;

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class GenericEntityESIndexFinder implements GenericEntityIndexFinderInterface
{
    private const MAX_RESULTS_ES = 10000;

    public function __construct(private $client)
    {
    }

    public function findAllByOrder(EntityIndexConfiguration $entityIndexConfiguration): Traversable
    {
        $resultsData = [];
        $params = [
            'index' => $entityIndexConfiguration->getTableName(),
            '_source' => [
                implode(',', $entityIndexConfiguration->getColumnsName())
            ],
            'scroll' => '1m',
            'size' => self::MAX_RESULTS_ES,
            'sort' => $entityIndexConfiguration->getIdentifierFieldName(),
        ];
        //Filter
        if ($entityIndexConfiguration->getFilterFieldName()!==null) {
            $params['q'] = $entityIndexConfiguration->getFilterFieldName();
        }

        $results = $this->client->search($params);
        $scrollId = $results['_scroll_id'];
        $totalResults['hits'] = [];
        do {
            $totalResults['hits'] = array_merge($totalResults['hits'], $results['hits']['hits']);
            $results = $this->client->scroll(['scroll_id'=>$scrollId, 'scroll'=>'1m']);
            $resultsPage = $results['hits']['hits'];
            $scrollId = $results['_scroll_id'];
        } while (count($resultsPage)>0);

        //Retrieve data in correct format
        if (isset($totalResults)) {
            foreach ($totalResults as $docs) {
                foreach ($docs as $value) {
                    if ($entityIndexConfiguration->getDateFieldName() !== null) {
                        $dateField = $entityIndexConfiguration->getDateFieldName();
                        $dateFormat = $entityIndexConfiguration->getDataProcessing();
                        $value["_source"][$dateField] = $dateFormat($value["_source"][$dateField]);
                        $resultsData[] = IndexResultsFactory::initIndexDateResults($value["_source"][$entityIndexConfiguration->getIdentifierFieldName()], $entityIndexConfiguration->getDateFieldName()?$value["_source"][$entityIndexConfiguration->getDateFieldName()]:null);
                    }
                }
            }
        }
        return new \ArrayIterator($resultsData);
    }
}
