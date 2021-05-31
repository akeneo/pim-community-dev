<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Connector\Api\Asset\JsonSchema;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;

/**
 * Validate the asset values grouped by attribute type.
 * It's more efficient than validate the values one by one.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AssetValuesValidator
{
    private FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier;

    private AssetValueValidatorRegistry $assetValueValidatorRegistry;

    public function __construct(
        AssetValueValidatorRegistry $assetValueValidatorRegistry,
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier
    ) {
        $this->assetValueValidatorRegistry = $assetValueValidatorRegistry;
        $this->findAttributesIndexedByIdentifier = $findAttributesIndexedByIdentifier;
    }

    public function validate(AssetFamilyIdentifier $assetFamilyIdentifier, array $normalizedAsset): array
    {
        $assetValues = $normalizedAsset['values'];
        $attributeCodesIndexedByTypes = $this->getAttributeCodesIndexedByType($assetFamilyIdentifier);
        $errors = [];

        foreach ($attributeCodesIndexedByTypes as $attributeType => $attributeCodes) {
            $assetValuesByType = array_intersect_key($assetValues, array_flip($attributeCodes));

            if (!empty($assetValuesByType)) {
                $assetValueValidator = $this->assetValueValidatorRegistry->getValidator($attributeType);
                $normalizedAssetWithFilteredValues = array_replace($normalizedAsset, ['values' => $assetValuesByType]);
                $errors = array_merge($errors, $assetValueValidator->validate($normalizedAssetWithFilteredValues));
            }
        }

        return $errors;
    }

    private function getAttributeCodesIndexedByType(AssetFamilyIdentifier $assetFamilyIdentifier): array
    {
        $attributes = $this->findAttributesIndexedByIdentifier->find($assetFamilyIdentifier);
        $attributeCodesIndexedByTypes = [];

        foreach ($attributes as $attribute) {
            $attributeCodesIndexedByTypes[get_class($attribute)][] = (string) $attribute->getCode();
        }

        return $attributeCodesIndexedByTypes;
    }
}
