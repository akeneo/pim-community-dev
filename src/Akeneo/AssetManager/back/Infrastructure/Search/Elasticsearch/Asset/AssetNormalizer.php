<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset;

use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\AssetManager\Domain\Query\Asset\SearchableAssetItem;
use Akeneo\AssetManager\Domain\Query\Attribute\FindValueKeysByAttributeTypeInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\FindValueKeysToIndexForAllChannelsAndLocalesInterface;
use Akeneo\AssetManager\Domain\Query\Locale\FindActivatedLocalesInterface;
use Akeneo\AssetManager\Domain\Repository\AssetNotFoundException;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\SqlFindSearchableAssets;

/**
 * Generates a representation of a asset for the search engine.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AssetNormalizer implements AssetNormalizerInterface
{
    private const IDENTIFIER = 'identifier';
    private const CODE = 'code';
    private const ASSET_FAMILY_CODE = 'asset_family_code';
    private const ASSET_FULL_TEXT_SEARCH = 'asset_full_text_search';
    private const UPDATED_AT = 'updated_at';
    private const ASSET_CODE_LABEL_SEARCH = 'asset_code_label_search';
    private const COMPLETE_VALUE_KEYS = 'complete_value_keys';
    private const VALUES_FIELD = 'values';

    private FindValueKeysToIndexForAllChannelsAndLocalesInterface $findValueKeysToIndexForAllChannelsAndLocales;

    private SqlFindSearchableAssets $findSearchableAssets;

    private FindValueKeysByAttributeTypeInterface $findValueKeysByAttributeType;

    private FindActivatedLocalesInterface $findActivatedLocales;

    public function __construct(
        FindValueKeysToIndexForAllChannelsAndLocalesInterface $findValueKeysToIndexForAllChannelsAndLocales,
        SqlFindSearchableAssets $findSearchableAssets,
        FindValueKeysByAttributeTypeInterface $findValueKeysByAttributeType,
        FindActivatedLocalesInterface $findActivatedLocales
    ) {
        $this->findValueKeysToIndexForAllChannelsAndLocales = $findValueKeysToIndexForAllChannelsAndLocales;
        $this->findSearchableAssets = $findSearchableAssets;
        $this->findValueKeysByAttributeType = $findValueKeysByAttributeType;
        $this->findActivatedLocales = $findActivatedLocales;
    }

    public function normalizeAsset(AssetIdentifier $assetIdentifier): array
    {
        $searchableAssetItem = $this->findSearchableAssets->byAssetIdentifier($assetIdentifier);
        if (null === $searchableAssetItem) {
            throw AssetNotFoundException::withIdentifier($assetIdentifier);
        }
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($searchableAssetItem->assetFamilyIdentifier);
        $matrixWithValueKeys = $this->findValueKeysToIndexForAllChannelsAndLocales->find($assetFamilyIdentifier);
        $activatedLocaleCodes = $this->findActivatedLocales->findAll();

        $fullTextMatrix = $this->fillMatrix($matrixWithValueKeys, $searchableAssetItem);
        $codeLabelMatrix = $this->createCodeLabelMatrix($searchableAssetItem, $activatedLocaleCodes);
        $filledValueKeysMatrix = $this->generateFilledValueKeys($searchableAssetItem);
        $filterableValues = $this->generateFilterableValues($searchableAssetItem);

        return $this->normalize(
            $searchableAssetItem,
            $fullTextMatrix,
            $codeLabelMatrix,
            $filledValueKeysMatrix,
            $filterableValues
        );
    }

    public function normalizeAssets(AssetFamilyIdentifier $assetFamilyIdentifier, array $assetIdentifiers): array
    {
        $normalizedAssets = [];
        $matrixWithValueKeys = $this->findValueKeysToIndexForAllChannelsAndLocales->find($assetFamilyIdentifier);
        $activatedLocaleCodes = $this->findActivatedLocales->findAll();
        $searchableAssetItems = $this->findSearchableAssets->byAssetIdentifiers($assetIdentifiers);
        foreach ($searchableAssetItems as $searchableAssetItem) {
            $fullTextMatrix = $this->fillMatrix($matrixWithValueKeys, $searchableAssetItem);
            $codeLabelMatrix = $this->createCodeLabelMatrix($searchableAssetItem, $activatedLocaleCodes);
            $filledValueKeysMatrix = $this->generateFilledValueKeys($searchableAssetItem);
            $valueKeysToFilterOn = $this->generateFilterableValues($searchableAssetItem);

            $normalizedAssets[] = $this->normalize(
                $searchableAssetItem,
                $fullTextMatrix,
                $codeLabelMatrix,
                $filledValueKeysMatrix,
                $valueKeysToFilterOn
            );
        }

        return $normalizedAssets;
    }

    private function createCodeLabelMatrix(SearchableAssetItem $searchableAssetItem, array $activatedLocaleCodes): array
    {
        $matrix = [];

        foreach ($activatedLocaleCodes as $activatedLocaleCode) {
            $label = $searchableAssetItem->labels[$activatedLocaleCode] ?? '';
            $matrix[$activatedLocaleCode] = trim(sprintf('%s %s', $searchableAssetItem->code, $label));
        }

        return $matrix;
    }

    private function fillMatrix(array $matrix, SearchableAssetItem $searchableAssetItem): array
    {
        $searchAssetListMatrix = [];
        foreach ($matrix as $channelCode => $valueKeysPerLocales) {
            foreach ($valueKeysPerLocales as $localeCode => $valueKeys) {
                $searchAssetListMatrix[$channelCode][$localeCode] = $this->concatenateDataToIndex($searchableAssetItem, $valueKeys);
            }
        }

        return $searchAssetListMatrix;
    }

    private function concatenateDataToIndex(SearchableAssetItem $searchableAssetItem, array $valueKeys): string
    {
        $valuesToIndex = array_intersect_key($searchableAssetItem->values, array_flip($valueKeys));
        $dataToIndex = array_map(
            fn(array $value) => $value['data'],
            $valuesToIndex
        );

        $stringToIndex = implode(' ', $dataToIndex);
        $cleanedData = str_replace(["\r", "\n"], " ", $stringToIndex);
        $cleanedData = strip_tags(html_entity_decode($cleanedData));

        return sprintf('%s %s', $searchableAssetItem->code, $cleanedData);
    }

    private function generateFilledValueKeys(SearchableAssetItem $searchableAssetItem): array
    {
        return array_fill_keys(array_keys($searchableAssetItem->values), true);
    }

    private function normalize(
        SearchableAssetItem $searchableAssetItem,
        array $fullTextMatrix,
        array $codeLabelMatrix,
        array $filledValueKeysMatrix,
        array $filterableValues
    ): array {
        return [
            self::IDENTIFIER => $searchableAssetItem->identifier,
            self::CODE => $searchableAssetItem->code,
            self::ASSET_FAMILY_CODE => $searchableAssetItem->assetFamilyIdentifier,
            self::ASSET_FULL_TEXT_SEARCH => $fullTextMatrix,
            self::ASSET_CODE_LABEL_SEARCH => $codeLabelMatrix,
            self::UPDATED_AT => $searchableAssetItem->updatedAt->getTimestamp(),
            self::COMPLETE_VALUE_KEYS => $filledValueKeysMatrix,
            self::VALUES_FIELD => $filterableValues
        ];
    }

    private function generateFilterableValues(SearchableAssetItem $searchableAssetItem): array
    {
        $valueKeys = $this->findValueKeysByAttributeType->find(
            AssetFamilyIdentifier::fromString($searchableAssetItem->assetFamilyIdentifier),
            [
                OptionAttribute::ATTRIBUTE_TYPE,
                OptionCollectionAttribute::ATTRIBUTE_TYPE
            ]
        );
        $result = [];
        foreach ($valueKeys as $valueKey) {
            if (isset($searchableAssetItem->values[$valueKey])) {
                $result[$valueKey] = $searchableAssetItem->values[$valueKey]['data'];
            }
        }

        return $result;
    }
}
