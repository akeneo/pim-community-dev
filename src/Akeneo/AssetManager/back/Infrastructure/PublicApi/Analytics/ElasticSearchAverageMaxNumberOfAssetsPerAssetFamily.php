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
    private int $assetFamilyLimit;

    public function __construct(Client $assetClient, int $assetFamilyLimit)
    {
        $this->assetClient = $assetClient;
        $this->assetFamilyLimit = $assetFamilyLimit;
    }

    public function fetch(): AverageMaxVolumes
    {
        $response = $this->assetClient->search([
            "aggs" => [
                "assets_by_asset_family_code" => [
                    "terms" => [
                        "field" => "asset_family_code",
                        "size" => $this->assetFamilyLimit
                    ]
                ],
                "avg_asset_by_family" => [
                    "avg_bucket" => [
                        "buckets_path" => "assets_by_asset_family_code._count"
                    ]
                ],
                "max_asset_by_family" => [
                    "max_bucket" => [
                        "buckets_path" => "assets_by_asset_family_code._count"
                    ]
                ]
            ]
        ]);

        $volume = new AverageMaxVolumes(
            (int) $response['aggregations']['max_asset_by_family']['value'],
            (int) ceil($response['aggregations']['avg_asset_by_family']['value'])
        );

        return $volume;
    }
}
