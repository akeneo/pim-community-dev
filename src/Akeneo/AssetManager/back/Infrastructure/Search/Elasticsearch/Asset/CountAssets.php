<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\CountAssetsInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class CountAssets implements CountAssetsInterface
{
    /** @var Client */
    private $assetClient;

    public function __construct(Client $assetClient)
    {
        $this->assetClient = $assetClient;
    }

    public function forAssetFamily(AssetFamilyIdentifier $assetFamilyIdentifier): int
    {
        $elasticSearchQuery = $this->getElasticSearchQuery($assetFamilyIdentifier);
        $matches = $this->assetClient->search($elasticSearchQuery);

        return $matches['hits']['total'];
    }

    private function getElasticSearchQuery(AssetFamilyIdentifier $assetFamilyIdentifier): array
    {
        return [
            '_source' => '_id',
            'query'   => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'term' => [
                                        'asset_family_code' => (string) $assetFamilyIdentifier,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
