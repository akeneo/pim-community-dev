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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Normalizer;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Read\AttributeOptionsMapping;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class OptionsMappingNormalizer
{
    /**
     * @param AttributeOptionsMapping $attributeOptionsMapping
     *
     * @return array
     */
    public function normalize(AttributeOptionsMapping $attributeOptionsMapping): array
    {
        return [
            'family' => $attributeOptionsMapping->familyCode(),
            'franklinAttributeCode' => $attributeOptionsMapping->franklinAttributeId(),
            'mapping' => $this->normalizeMapping($attributeOptionsMapping->mapping()),
        ];
    }

    /**
     * @param array $attributeOptionsMapping
     *
     * @return array
     */
    private function normalizeMapping(array $attributeOptionsMapping): array
    {
        $normalizedMapping = [];
        foreach ($attributeOptionsMapping as $attributeOptionMapping) {
            $normalizedMapping[$attributeOptionMapping->franklinAttributeId()] = [
                'franklinAttributeOptionCode' => [
                    'label' => $attributeOptionMapping->franklinAttributeLabel(),
                ],
                'catalogAttributeOptionCode' => $attributeOptionMapping->catalogAttributeCode(),
                'status' => $attributeOptionMapping->status(),
            ];
        }

        return $normalizedMapping;
    }
}
