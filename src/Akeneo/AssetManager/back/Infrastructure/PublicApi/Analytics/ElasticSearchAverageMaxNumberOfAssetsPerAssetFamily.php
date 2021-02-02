<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\PublicApi\Analytics;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class ElasticSearchAverageMaxNumberOfAssetsPerAssetFamily
{
    private Client $assetClient;

    public function __construct(Client $assetClient)
    {
        $this->assetClient = $assetClient;
    }

    public function fetch(): AverageMaxVolumes
    {
        $response = $this->assetClient->search([
            "aggs" => [
                "by_asset_family_code" => [
                    "terms" => [
                        "field" => "asset_family_code"
                    ],
                ]
            ],
        ]);

        $assetCount = array_map(
            fn($assetFamilyData) => $assetFamilyData['doc_count'],
            $response['aggregations']['by_asset_family_code']['buckets']
        );

        $volume = new AverageMaxVolumes(
            (int) max($assetCount),
            (int) ceil(array_sum($assetCount) / count($assetCount))
        );

        return $volume;
    }
}
