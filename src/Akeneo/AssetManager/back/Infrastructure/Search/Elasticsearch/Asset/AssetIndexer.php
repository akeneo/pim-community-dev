<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset;

use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\FindAssetIdentifiersByAssetFamilyInterface;
use Akeneo\AssetManager\Domain\Query\Asset\SearchableAssetItem;
use Akeneo\AssetManager\Domain\Repository\AssetIndexerInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\SqlFindAssetIdentifiersByAssetFamily;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AssetIndexer implements AssetIndexerInterface
{
    private const KEY_AS_ID = 'identifier';

    /** @var Client */
    private $assetClient;

    /** @var AssetNormalizerInterface */
    private $normalizer;

    /** @var int */
    private $batchSize;

    /** @TODO pull up: remove null in master */
    /** @var FindAssetIdentifiersByAssetFamilyInterface|null */
    private $assetIdentifiersByAssetFamily;

    /** @TODO pull up remove default value of $assetIdentifiersByAssetFamily in master */
    public function __construct(
        Client $assetClient,
        AssetNormalizerInterface $normalizer,
        int $batchSize,
        FindAssetIdentifiersByAssetFamilyInterface $assetIdentifiersByAssetFamily = null
    ) {
        $this->assetClient = $assetClient;
        $this->normalizer = $normalizer;
        $this->batchSize = $batchSize;
        $this->assetIdentifiersByAssetFamily = $assetIdentifiersByAssetFamily;
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

        $assetsToIndexByBatch = array_chunk($normalizedSearchableAssets, $this->batchSize);
        foreach ($assetsToIndexByBatch as $assetsToIndex) {
            $this->assetClient->bulkIndexes($assetsToIndex, self::KEY_AS_ID, refresh::disable());
        }
    }

    public function indexByAssetFamily(AssetFamilyIdentifier $assetFamilyIdentifier): void
    {
        /** @TODO pull up remove if in master */
        if ($this->assetIdentifiersByAssetFamily === null || !$this->normalizer instanceof AssetNormalizer) {
            $normalizedSearchableAssets = $this->normalizer->normalizeAssetsByAssetFamily($assetFamilyIdentifier);
            $this->legacyBulkIndexAssets($normalizedSearchableAssets);

            return;
        }

        $assetIdentifiers = $this->assetIdentifiersByAssetFamily->find($assetFamilyIdentifier);
        $this->bulkIndexAssets($assetFamilyIdentifier, $assetIdentifiers);
    }

    /**
     * {@inheritdoc}
     */
    public function removeByAssetFamilyIdentifier(string $assetFamilyIdentifier)
    {
        $queryBody = [
            'query' => [
                'match' => ['asset_family_code' => $assetFamilyIdentifier],
            ],
        ];

        $this->assetClient->deleteByQuery($queryBody);
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
                            ['term' => ['code' => $assetCode]],
                        ],
                ],
            ],
        ];

        $this->assetClient->deleteByQuery($queryBody);
    }

    public function refresh(): void
    {
        $this->assetClient->refreshIndex();
    }

    private function bulkIndexAssets(AssetFamilyIdentifier $assetFamilyIdentifier, iterable $assetIdentifiers)
    {
        $assetIdentifierToNormalize = [];
        foreach ($assetIdentifiers as $assetIdentifier) {
            $assetIdentifierToNormalize[] = $assetIdentifier;
            if (\count($assetIdentifierToNormalize) % $this->batchSize === 0) {
                $normalizedSearchableAssets = $this->normalizer->normalizeAssets(
                    $assetFamilyIdentifier,
                    $assetIdentifierToNormalize
                );

                $this->assetClient->bulkIndexes($normalizedSearchableAssets, self::KEY_AS_ID, refresh::disable());
                $assetIdentifierToNormalize = [];
            }
        }

        if (!empty($assetIdentifierToNormalize)) {
            $normalizedSearchableAssets = $this->normalizer->normalizeAssets(
                $assetFamilyIdentifier,
                $assetIdentifierToNormalize
            );

            $this->assetClient->bulkIndexes($normalizedSearchableAssets, self::KEY_AS_ID, refresh::disable());
        }
    }

    /** @TODO pull up remove this function in master */
    private function legacyBulkIndexAssets(iterable $normalizedSearchableAssets)
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
