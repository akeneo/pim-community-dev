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

            $status = DomainAttributeMapping::ATTRIBUTE_MAPPED === $attributeMapping->getStatus()
                ? AttributeMapping::STATUS_ACTIVE : AttributeMapping::STATUS_INACTIVE;
            $result[] = [
                'from' => ['id' => $attributeMapping->getTargetAttributeCode()],
                'to' => $normalizedPimAttribute,
                'status' => $status,
            ];
        }

        return $result;
    }
}
