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

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Write\AttributeMapping as DomainAttributeMapping;
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
     * @param DomainAttributeMapping[] $attributesMapping
     *
     * @return array
     */
    public function normalize(array $attributesMapping): array
    {
        $result = [];
        foreach ($attributesMapping as $attributeMapping) {
            $status = DomainAttributeMapping::ATTRIBUTE_MAPPED === $attributeMapping->getStatus()
                ? AttributeMapping::STATUS_ACTIVE : AttributeMapping::STATUS_INACTIVE;

            $result[] = [
                'from' => ['id' => $attributeMapping->getTargetAttributeCode()],
                'to' => $this->computeNormalizedAttribute($attributeMapping),
                'status' => $status,
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
}
