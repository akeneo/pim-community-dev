<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Elasticsearch;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\CountQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\CountVolume;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CountProductModels implements CountQuery
{
    private const VOLUME_NAME = 'count_product_models';

    /** @var Client */
    private $client;

    /** @var int */
    private $limit;

    public function __construct(Client $client, int $limit)
    {
        $this->client = $client;
        $this->limit = $limit;
    }

    public function fetch(): CountVolume
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'term' => [
                                'document_type' => ProductModelInterface::class,
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $result = $this->client->count($query);

        return new CountVolume((int)$result['count'], $this->limit, self::VOLUME_NAME);
    }
}
