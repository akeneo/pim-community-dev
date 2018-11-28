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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Normalizer;

use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Model\Write\AttributeMapping as DomainAttributeMapping;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\ValueObject\AttributeMapping;
use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * Prepare AttributesMapping model from Domain layer in order to be used by Franklin client.
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class AttributesMappingNormalizer
{
    /**
     * @param DomainAttributeMapping[] $attributesMapping
     *
     * @return array
     */
    public function normalize(array $attributesMapping): array
    {
        $statusMapping = [
            DomainAttributeMapping::ATTRIBUTE_PENDING => AttributeMapping::STATUS_PENDING,
            DomainAttributeMapping::ATTRIBUTE_MAPPED => AttributeMapping::STATUS_ACTIVE,
            DomainAttributeMapping::ATTRIBUTE_UNMAPPED => AttributeMapping::STATUS_INACTIVE,
        ];

        $result = [];
        foreach ($attributesMapping as $attributeMapping) {
            $result[] = [
                'from' => ['id' => $attributeMapping->getTargetAttributeCode()],
                'to' => $this->computeNormalizedAttribute($attributeMapping),
                'status' => $statusMapping[$attributeMapping->getStatus()],
            ];
        }

        return $result;
    }

    /**
     * @param DomainAttributeMapping $attributeMapping
     *
     * @return array|null
     */
    private function computeNormalizedAttribute(DomainAttributeMapping $attributeMapping): ?array
    {
        $attribute = $attributeMapping->getAttribute();

        $normalizedPimAttribute = null;
        if (null !== $attribute) {
            $labels = [];
            $translations = $attribute->getTranslations();
            if (!$translations->isEmpty()) {
                foreach ($translations as $translation) {
                    $labels[$translation->getLocale()] = $translation->getLabel();
                }
            }

            $attributeTypes = array_flip(DomainAttributeMapping::ATTRIBUTE_TYPES_MAPPING);

            $normalizedPimAttribute = [
                'id' => $attribute->getCode(),
                'label' => $labels,
                'type' => $attributeTypes[$attribute->getType()],
            ];

            if (AttributeTypes::METRIC === $attribute->getType()) {
                $normalizedPimAttribute['unit'] = $attribute->getDefaultMetricUnit();
            }
        }

        return $normalizedPimAttribute;
    }
}
