<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\AssetManager\Component\Connector\FlatTranslator\AttributeValueTranslator;

use Akeneo\AssetManager\Infrastructure\PublicApi\Enrich\FindAssetLabelTranslationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AttributeValue\FlatAttributeValueTranslatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatTranslatorInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssetCollectionTranslator implements FlatAttributeValueTranslatorInterface
{
    private FindAssetLabelTranslationInterface $findAssetLabelTranslation;

    public function __construct(FindAssetLabelTranslationInterface $findAssetLabelTranslation)
    {
        $this->findAssetLabelTranslation = $findAssetLabelTranslation;
    }

    public function supports(string $attributeType, string $columnName): bool
    {
        return AttributeTypes::ASSET_COLLECTION === $attributeType;
    }

    public function translate(string $attributeCode, array $properties, array $values, string $locale): array
    {
        if (!isset($properties['reference_data_name'])) {
            throw new \LogicException('Expected properties to have a reference data name to translate asset collection values to flat');
        }

        $assetCodes = $this->extractAssetCodes($values);

        $familyCode = $properties['reference_data_name'];
        $assetTranslations = $this->findAssetLabelTranslation->byFamilyCodeAndAssetCodes(
            $familyCode,
            $assetCodes,
            $locale
        );

        $result = [];
        foreach ($values as $valueIndex => $value) {
            if (empty($value)) {
                $result[$valueIndex] = $value;
                continue;
            }

            $currentAssetCodes = explode(',', $value);

            $assetLabels = [];
            foreach ($currentAssetCodes as $currentAssetCode) {
                $assetLabels[] = $assetTranslations[$currentAssetCode]
                    ?? $this->codeWithFallbackPattern($currentAssetCode);
            }

            $result[$valueIndex] = implode(',', $assetLabels);
        }

        return $result;
    }

    private function extractAssetCodes(array $values): array
    {
        $assetCodes = [];

        foreach ($values as $value) {
            $currentAssetCodes = explode(',', $value);

            $assetCodes = array_merge($assetCodes, $currentAssetCodes);
        }

        return array_unique($assetCodes);
    }

    private function codeWithFallbackPattern(string $currentAssetCode): string
    {
        return sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, $currentAssetCode);
    }
}
