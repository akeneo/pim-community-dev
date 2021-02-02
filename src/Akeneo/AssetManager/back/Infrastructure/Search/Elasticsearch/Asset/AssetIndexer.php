<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset;

use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetIndexerInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AssetIndexer implements AssetIndexerInterface
{
    private const KEY_AS_ID = 'identifier';

    private Client $assetClient;
    private AssetNormalizerInterface $normalizer;
    private int $batchSize;

    public function __construct(
        Client $assetClient,
        AssetNormalizerInterface $normalizer,
        int $batchSize
    ) {
        $this->assetClient = $assetClient;
        $this->normalizer = $normalizer;
        $this->batchSize = $batchSize;
    }

    /**
     * {@inheritdoc}
     */
    public function index(AssetIdentifier $assetIdentifier): void
    {
        $normalizedAsset = $this->normalizer->normalizeAsset($assetIdentifier);
        $this->assetClient->index($normalizedAsset['identifier'], $normalizedAsset, refresh::disable());
    }

    /**
     * {@inheritdoc}
     */
    public function indexByAssetIdentifiers(array $assetIdentifiers)
    {
        $normalizedSearchableAssets = array_map(function (AssetIdentifier $assetIdentifier) {
            return $this->normalizer->normalizeAsset($assetIdentifier);
        }, array_unique($assetIdentifiers));

        $this->bulkIndexAssets($normalizedSearchableAssets);
    }

    public function indexByAssetFamily(AssetFamilyIdentifier $assetFamilyIdentifier): void
    {
        $normalizedSearchableAssets = $this->normalizer->normalizeAssetsByAssetFamily($assetFamilyIdentifier);
        $this->bulkIndexAssets($normalizedSearchableAssets);
    }

    /**
     * {@inheritdoc}
     */
    public function removeAssetByAssetFamilyIdentifierAndCode(
        string $assetFamilyIdentifier,
        string $assetCode
    ) {
        $queryBody = [
            'query' => [
                'bool' => [
                    'must' =>
                        [
                            ['term' => ['asset_family_code' => $assetFamilyIdentifier]],
                            ['terms' => ['code' => $assetCode]],
                        ],
                ],
            ],
        ];

        $this->assetClient->deleteByQuery($queryBody);
    }

    public function removeAssetByAssetFamilyIdentifierAndCodes(string $assetFamilyIdentifier, array $assetCodes)
    {
        $queryBody = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'must' =>
                                [
                                    ['term' => ['asset_family_code' => $assetFamilyIdentifier]],
                                    ['terms' => ['code' => $assetCodes]],
                                ],
                        ],
                    ]
                ]
            ],
        ];

        $this->assetClient->deleteByQuery($queryBody);
    }

    public function refresh(): void
    {
        $this->assetClient->refreshIndex();
    }

    private function bulkIndexAssets(iterable $normalizedSearchableAssets)
    {
        $toIndex = [];
        foreach ($normalizedSearchableAssets as $normalizedSearchableAsset) {
            $toIndex[] = $normalizedSearchableAsset;

            if (\count($toIndex) % $this->batchSize === 0) {
                $this->assetClient->bulkindexes($toIndex, self::KEY_AS_ID, refresh::disable());
                $toIndex = [];
            }
        }

        if (!empty($toIndex)) {
            $this->assetClient->bulkindexes($toIndex, self::KEY_AS_ID, refresh::disable());
        }
    }
}
