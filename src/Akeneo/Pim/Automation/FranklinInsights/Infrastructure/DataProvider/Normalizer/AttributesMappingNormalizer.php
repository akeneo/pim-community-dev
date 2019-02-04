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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Normalizer;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\AttributeMappingStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Write\AttributeMapping as DomainAttributeMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Write\AttributesMapping;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\AttributeMapping;
use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * Prepare AttributesMapping model from Domain layer in order to be used by Franklin client.
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class AttributesMappingNormalizer
{
    /**
     * @param AttributesMapping $attributesMapping
     *
     * @return array
     */
    public function normalize(AttributesMapping $attributesMapping): array
    {
        $result = [];
        foreach ($attributesMapping->mapping() as $attributeMapping) {
            $result[] = [
                'from' => ['id' => $attributeMapping->getTargetAttributeCode()],
                'to' => $this->computeNormalizedAttribute($attributeMapping),
                'status' => $this->computeAttributeStatus($attributeMapping),
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
            foreach ($attribute->getTranslations() as $translation) {
                $labels[$translation->getLocale()] = $translation->getLabel();
            }

            $normalizedPimAttribute = [
                'id' => $attribute->getCode(),
                'label' => $labels,
                'type' => DomainAttributeMapping::AUTHORIZED_ATTRIBUTE_TYPE_MAPPINGS[$attribute->getType()],
            ];

            if (AttributeTypes::METRIC === $attribute->getType()) {
                $normalizedPimAttribute['unit'] = $attribute->getDefaultMetricUnit();
            }
        }

        return $normalizedPimAttribute;
    }

    /**
     * @param DomainAttributeMapping $attributeMapping
     *
     * @return string
     */
    private function computeAttributeStatus(DomainAttributeMapping $attributeMapping): string
    {
        switch ($attributeMapping->getStatus()) {
            case AttributeMappingStatus::ATTRIBUTE_MAPPED:
                return AttributeMapping::STATUS_ACTIVE;
            case AttributeMappingStatus::ATTRIBUTE_PENDING:
                return AttributeMapping::STATUS_PENDING;
            case AttributeMappingStatus::ATTRIBUTE_UNMAPPED:
                return AttributeMapping::STATUS_INACTIVE;
        }
    }
}
