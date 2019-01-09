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

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributesMappingResponse;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributesMappingNormalizer
{
    /**
     * @param AttributesMappingResponse $attributesMappingResponse
     *
     * @return array
     */
    public function normalize(AttributesMappingResponse $attributesMappingResponse): array
    {
        $normalizedAttributes = [];
        foreach ($attributesMappingResponse as $attribute) {
            $normalizedAttributes[$attribute->getTargetAttributeCode()] = [
                'franklinAttribute' => [
                    'label' => $attribute->getTargetAttributeLabel(),
                    'type' => $attribute->getTargetAttributeType(),
                    'summary' => $attribute->getSummary() ?? [],
                ],
                'attribute' => $attribute->getPimAttributeCode(),
                'status' => $attribute->getStatus(),
            ];
        }

        return $normalizedAttributes;
    }
}
