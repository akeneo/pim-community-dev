<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Elasticsearch;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\CountQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\CountVolume;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CountVariantProducts implements CountQuery
{
    private const VOLUME_NAME = 'count_variant_products';

    /** @var Client */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function fetch(): CountVolume
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'term' => [
                                'document_type' => ProductInterface::class,
                            ],
                        ],
                        [
                            'exists' => [
                                'field' => 'parent',
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $result = $this->client->count($query);

        return new CountVolume((int)$result['count'], self::VOLUME_NAME);
    }
}
