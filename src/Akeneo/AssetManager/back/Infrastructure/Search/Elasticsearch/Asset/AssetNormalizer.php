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

    /** @var FindValueKeysToIndexForAllChannelsAndLocalesInterface */
    private $findValueKeysToIndexForAllChannelsAndLocales;

    /** @var SqlFindSearchableAssets */
    private $findSearchableAssets;

    /** @var FindValueKeysByAttributeTypeInterface */
    private $findValueKeysByAttributeType;

    /** @var FindActivatedLocalesInterface */
    private $findActivatedLocales;

    /**
     * @todo @merge master/5.0: remove the default null value for the FindActivatedLocalesInterface argument
     */
    public function __construct(
        FindValueKeysToIndexForAllChannelsAndLocalesInterface $findValueKeysToIndexForAllChannelsAndLocales,
        SqlFindSearchableAssets $findSearchableAssets,
        FindValueKeysByAttributeTypeInterface $findValueKeysByAttributeType,
        FindActivatedLocalesInterface $findActivatedLocales = null
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

    /** @TODO pull up remove this function in master */
    public function normalizeAssetsByAssetFamily(AssetFamilyIdentifier $assetFamilyIdentifier): \Iterator
    {
        $matrixWithValueKeys = $this->findValueKeysToIndexForAllChannelsAndLocales->find($assetFamilyIdentifier);
        $activatedLocaleCodes = $this->findActivatedLocales->findAll();
        $searchableAssetItems = $this->findSearchableAssets->byAssetFamilyIdentifier($assetFamilyIdentifier);
        foreach ($searchableAssetItems as $searchableAssetItem) {
            $fullTextMatrix = $this->fillMatrix($matrixWithValueKeys, $searchableAssetItem);
            $codeLabelMatrix = $this->createCodeLabelMatrix($searchableAssetItem, $activatedLocaleCodes);
            $filledValueKeysMatrix = $this->generateFilledValueKeys($searchableAssetItem);
            $valueKeysToFilterOn = $this->generateFilterableValues($searchableAssetItem);

            yield $this->normalize(
                $searchableAssetItem,
                $fullTextMatrix,
                $codeLabelMatrix,
                $filledValueKeysMatrix,
                $valueKeysToFilterOn
            );
        }
    }

    private function createCodeLabelMatrix(SearchableAssetItem $searchableAssetItem, array $activatedLocaleCodes): array
    {
        $matrix = [];

        // @todo @merge master/5.0: remove this case
        if (null === $this->findActivatedLocales) {
            foreach ($searchableAssetItem->labels as $localeCode => $label) {
                $matrix[$localeCode] = sprintf('%s %s', $searchableAssetItem->code, $label);
            }

            return $matrix;
        }

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
            function (array $value) {
                return $value['data'];
            },
            $valuesToIndex
        );

        $stringToIndex = implode(' ', $dataToIndex);
        $cleanedData = str_replace(["\r", "\n"], " ", $stringToIndex);
        $cleanedData = strip_tags(html_entity_decode($cleanedData));
        $result = sprintf('%s %s', $searchableAssetItem->code, $cleanedData);

        return $result;
    }

    private function now(): int
    {
        return (new \DateTime('now', new \DateTimeZone('UTC')))->getTimestamp();
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
        $normalizedAsset = [
            self::IDENTIFIER => $searchableAssetItem->identifier,
            self::CODE => $searchableAssetItem->code,
            self::ASSET_FAMILY_CODE => $searchableAssetItem->assetFamilyIdentifier,
            self::ASSET_FULL_TEXT_SEARCH => $fullTextMatrix,
            self::ASSET_CODE_LABEL_SEARCH => $codeLabelMatrix,
            self::UPDATED_AT => $this->now(),
            self::COMPLETE_VALUE_KEYS => $filledValueKeysMatrix,
            self::VALUES_FIELD => $filterableValues
        ];

        return $normalizedAsset;
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
