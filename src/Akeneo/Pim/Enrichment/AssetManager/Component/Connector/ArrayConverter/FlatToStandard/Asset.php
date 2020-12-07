<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\AssetManager\Component\Connector\ArrayConverter\FlatToStandard;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\NumberAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\AssetManager\Domain\Query\Attribute\AttributeDetails;
use Akeneo\AssetManager\Domain\Query\Attribute\FindAttributesDetailsInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Akeneo\Tool\Component\Connector\Exception\DataArrayConversionException;
use Webmozart\Assert\Assert;

class Asset implements ArrayConverterInterface
{
    const DIRECTORY_PATH_OPTION_KEY = 'directory_path';

    private FieldsRequirementChecker $fieldsChecker;
    private FindAttributesDetailsInterface $findAttributeDetails;
    private array $cachedAttributes = [];

    public function __construct(
        FieldsRequirementChecker $fieldsChecker,
        FindAttributesDetailsInterface $findAttributeDetails
    ) {
        $this->fieldsChecker = $fieldsChecker;
        $this->findAttributeDetails = $findAttributeDetails;
    }

    public function convert(array $item, array $options = [])
    {
        Assert::keyExists($options, self::DIRECTORY_PATH_OPTION_KEY);
        Assert::string($options[self::DIRECTORY_PATH_OPTION_KEY]);
        $this->fieldsChecker->checkFieldsPresence($item, ['assetFamilyIdentifier', 'code']);

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($item['assetFamilyIdentifier'] ?? '');
        $convertedItem = ['values' => ['label' => []]];
        foreach ($item as $field => $data) {
            $convertedItem = $this->convertField(
                $convertedItem,
                $options[self::DIRECTORY_PATH_OPTION_KEY],
                $assetFamilyIdentifier,
                $field,
                $data
            );
        }

        return $convertedItem;
    }

    private function convertField(
        array $convertedItem,
        string $directoryPath,
        AssetFamilyIdentifier $assetFamilyIdentifier,
        string $field,
        $data
    ): array {
        if ('' === trim($field)) {
            return $convertedItem;
        }

        if ('assetFamilyIdentifier' === $field) {
            $convertedItem['asset_family_identifier'] = $data;

            return $convertedItem;
        } elseif ('code' === $field) {
            $convertedItem['code'] = $data;

            return $convertedItem;
        }

        $tokens = explode('-', $field);

        if ('label' === $tokens[0]) {
            if (!array_key_exists($tokens[0], $convertedItem['values'])) {
                $convertedItem['values'][$tokens[0]] = [];
            }

            $convertedItem['values']['label'][] = [
                'locale' => $tokens[1] ?? null,
                'channel' => null,
                'data' => $data,
            ];
        } else {
            $attributeDetails = $this->getAttributeDetails($tokens[0], $assetFamilyIdentifier);
            if (null === $attributeDetails) {
                // If attribute does not belong to asset family and the value is empty, we skip it.
                // This behavior allows to have assets with different asset families in the same import.
                if ('' === $data) {
                    return $convertedItem;
                }

                // On contrary when we try to put a non empty value in an attribute that does not belong to the
                // asset family, we throw an exception.
                throw new DataArrayConversionException(\sprintf(
                    'Unable to find the "%s" attribute in the "%s" asset family',
                    $tokens[0],
                    $assetFamilyIdentifier
                ));
            }

            if (!array_key_exists($tokens[0], $convertedItem['values'])) {
                $convertedItem['values'][$tokens[0]] = [];
            }

            $convertedItem['values'][$tokens[0]][] = $this->convertValue(
                $directoryPath,
                $attributeDetails,
                $field,
                $data
            );
        }

        return $convertedItem;
    }

    private function convertValue(
        string $directoryPath,
        AttributeDetails $attributeDetails,
        string $field,
        $data
    ): array {
        $tokens = explode('-', $field);
        if (OptionCollectionAttribute::ATTRIBUTE_TYPE === $attributeDetails->type) {
            $data = array_filter(explode(',', $data));
        } elseif (!empty($data) && MediaFileAttribute::ATTRIBUTE_TYPE === $attributeDetails->type) {
            $data = sprintf('%s%s%s', $directoryPath, DIRECTORY_SEPARATOR, $data);
        } elseif (NumberAttribute::ATTRIBUTE_TYPE === $attributeDetails->type) {
            $data = (string) $data;
        }

        $convertedValue = ['locale' => null, 'channel' => null, 'data' => $data];

        if ($attributeDetails->valuePerChannel && $attributeDetails->valuePerLocale) {
            $convertedValue['locale'] = $tokens[1] ?? null;
            $convertedValue['channel'] = $tokens[2] ?? null;
        } elseif ($attributeDetails->valuePerLocale) {
            $convertedValue['locale'] = $tokens[1] ?? null;
        } elseif ($attributeDetails->valuePerChannel) {
            $convertedValue['channel'] = $tokens[1] ?? null;
        }

        return $convertedValue;
    }

    private function getAttributeDetails(
        string $attributeCode,
        AssetFamilyIdentifier $assetFamilyIdentifier
    ): ?AttributeDetails {
        $normalizedAssetFamilyIdentifier = $assetFamilyIdentifier->normalize();
        if (!array_key_exists($normalizedAssetFamilyIdentifier, $this->cachedAttributes)) {
            $this->cachedAttributes[$normalizedAssetFamilyIdentifier] = $this
                ->getIndexedAttributes($assetFamilyIdentifier);
        }

        return $this->cachedAttributes[$normalizedAssetFamilyIdentifier][$attributeCode] ?? null;
    }

    private function getIndexedAttributes(AssetFamilyIdentifier $assetFamilyIdentifier): array
    {
        $attributesDetails = $this->findAttributeDetails->find($assetFamilyIdentifier);

        $indexedAttributeDetails = [];
        foreach ($attributesDetails as $attributeDetail) {
            $indexedAttributeDetails[$attributeDetail->code] = $attributeDetail;
        }

        return $indexedAttributeDetails;
    }
}
