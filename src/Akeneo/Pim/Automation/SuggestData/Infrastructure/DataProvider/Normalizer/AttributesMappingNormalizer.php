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

use Akeneo\Pim\Automation\SuggestData\Domain\Model\Write\AttributeMapping as DomainAttributeMapping;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject\AttributeMapping;

/**
 * Prepare AttributesMapping model from Domain layer in order to be used by Franklin client.
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class AttributesMappingNormalizer
{
    /** @var string[] */
    public const PIM_AI_MAPPING_STATUS = [
        DomainAttributeMapping::ATTRIBUTE_PENDING => AttributeMapping::STATUS_PENDING,
        DomainAttributeMapping::ATTRIBUTE_MAPPED => AttributeMapping::STATUS_ACTIVE,
        DomainAttributeMapping::ATTRIBUTE_UNMAPPED => AttributeMapping::STATUS_INACTIVE,
    ];

    /**
     * @param DomainAttributeMapping[] $attributesMapping
     *
     * @return array
     */
    public function normalize(array $attributesMapping): array
    {
        $result = [];
        foreach ($attributesMapping as $attributeMapping) {
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
                    'type' => 'text', // TODO: Should be managed in APAI-174
                ];
            }

            $result[] = [
                'from' => ['id' => $attributeMapping->getTargetAttributeCode()],
                'to' => $normalizedPimAttribute,
                'status' => static::PIM_AI_MAPPING_STATUS[$attributeMapping->getStatus()],
            ];
        }

        return $result;
    }
}
