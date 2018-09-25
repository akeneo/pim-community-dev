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

use Akeneo\Pim\Automation\SuggestData\Domain\Model\Write\AttributeMapping;

/**
 * Prepare AttributesMapping model from Domain layer in order to be used by PIM.ai client
 *
 * @author    Romain Monceau <romain@akeneo.com>
 */
class AttributesMappingNormalizer
{
    /**
     * @param AttributeMapping[] $attributesMapping
     *
     * @return array
     */
    public function normalize(array $attributesMapping): array
    {
        $result = [];
        foreach ($attributesMapping as $attributeMapping) {
            $attribute = $attributeMapping->getAttribute();

            if (null === $attribute) {
                $normalizedAttribute = null;
            } else {
                $attribute->setLocale('en_US');
                $normalizedAttribute = [
                    'id' => $attribute->getCode(),
                    'label' => [
                        'en_US' => $attribute->getLabel()
                    ],
                    'type' => 'text',// TODO: Should be managed in APAI-174
                ];
            }

            $result[] = [
                'from' => ['id' => $attributeMapping->getTargetAttributeCode()],
                'to' => $normalizedAttribute,
                'status' => $attributeMapping->getStatus(), //TODO: Should be managed in APAI-99
            ];
        }

        return $result;
    }
}
