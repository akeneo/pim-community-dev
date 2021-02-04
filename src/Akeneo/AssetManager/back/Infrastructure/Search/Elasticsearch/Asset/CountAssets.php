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

    public function all(): int
    {
        $matches = $this->assetClient->count([]);

        return $matches['count'];
    }

    public function forAssetFamily(AssetFamilyIdentifier $assetFamilyIdentifier): int
    {
        $elasticSearchQuery = $this->getElasticSearchQuery($assetFamilyIdentifier);
        $matches = $this->assetClient->count($elasticSearchQuery);

        return $matches['count'];
    }

    private function getElasticSearchQuery(AssetFamilyIdentifier $assetFamilyIdentifier): array
    {
        return [
            'query' => [
                'term' => [
                    'asset_family_code' => (string) $assetFamilyIdentifier,
                ],
            ],
        ];
    }
}
