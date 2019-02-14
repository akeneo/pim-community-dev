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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Normalizer;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\AttributeMappingStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributeMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributesMappingResponse;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Normalizer\AttributesMappingNormalizer;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributesMappingNormalizerSpec extends ObjectBehavior
{
    public function it_is_an_attributes_mapping_normalizer(): void
    {
        $this->shouldBeAnInstanceOf(AttributesMappingNormalizer::class);
    }

    public function it_normalizes_attributes_mapping(): void
    {
        $attributesMapping = new AttributesMappingResponse();
        $attributesMapping->addAttribute(new AttributeMapping(
            'product_weight',
            'Product Weight',
            'metric',
            null,
            AttributeMappingStatus::ATTRIBUTE_PENDING,
            ['23kg', '12kg']
        ));

        $expectedMapping = [
            'product_weight' => [
                'franklinAttribute' => [
                    'label' => 'Product Weight',
                    'type' => 'metric',
                    'summary' => ['23kg', '12kg'],
                ],
                'attribute' => null,
                'status' => AttributeMappingStatus::ATTRIBUTE_PENDING,
            ],
        ];

        $this->normalize($attributesMapping)->shouldReturn($expectedMapping);
    }

    public function it_normalizes_null_summary_as_empty_array(): void
    {
        $attributesMapping = new AttributesMappingResponse();
        $attributesMapping->addAttribute(
            new AttributeMapping(
                'product_weight',
                'Product Weight',
                'metric',
                null,
                AttributeMappingStatus::ATTRIBUTE_PENDING,
                null
            )
        );

        $expectedMapping = [
            'product_weight' => [
                'franklinAttribute' => [
                    'label' => 'Product Weight',
                    'type' => 'metric',
                    'summary' => [],
                ],
                'attribute' => null,
                'status' => AttributeMappingStatus::ATTRIBUTE_PENDING,
            ],
        ];

        $this->normalize($attributesMapping)->shouldReturn($expectedMapping);
    }
}
