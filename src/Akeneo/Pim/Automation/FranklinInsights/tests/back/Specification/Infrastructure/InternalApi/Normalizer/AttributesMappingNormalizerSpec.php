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
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributeMappingCollection;
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
        $attributesMapping = new AttributeMappingCollection();
        $attributesMapping
            ->addAttribute(new AttributeMapping(
                'product_weight',
                'Product Weight',
                'metric',
                null,
                AttributeMappingStatus::ATTRIBUTE_PENDING,
                ['23kg', '12kg']
            ))
            ->addAttribute(new AttributeMapping(
                'product_height',
                'Product Height',
                'metric',
                'height',
                AttributeMappingStatus::ATTRIBUTE_ACTIVE,
                ['1m', '2.8m']
            ))
            ->addAttribute(new AttributeMapping(
                'product_width',
                'Product Width',
                'metric',
                null,
                AttributeMappingStatus::ATTRIBUTE_PENDING,
                ['0.5m', '1.2m'],
                'width'
            ));

        $expectedMapping = [
            'product_weight' => [
                'franklinAttribute' => [
                    'code' => 'product_weight',
                    'label' => 'Product Weight',
                    'type' => 'metric',
                    'summary' => ['23kg', '12kg'],
                ],
                'attribute' => null,
                'status' => AttributeMappingStatus::ATTRIBUTE_PENDING,
                'exactMatchAttributeFromOtherFamily' => null,
                'canCreateAttribute' => true,
            ],
            'product_width' => [
                'franklinAttribute' => [
                    'code' => 'product_width',
                    'label' => 'Product Width',
                    'type' => 'metric',
                    'summary' => ['0.5m', '1.2m'],
                ],
                'attribute' => null,
                'status' => AttributeMappingStatus::ATTRIBUTE_PENDING,
                'exactMatchAttributeFromOtherFamily' => 'width',
                'canCreateAttribute' => false,
            ],
            'product_height' => [
                'franklinAttribute' => [
                    'code' => 'product_height',
                    'label' => 'Product Height',
                    'type' => 'metric',
                    'summary' => ['1m', '2.8m'],
                ],
                'attribute' => 'height',
                'status' => AttributeMappingStatus::ATTRIBUTE_ACTIVE,
                'exactMatchAttributeFromOtherFamily' => null,
                'canCreateAttribute' => false,
            ],
        ];

        $this->normalize($attributesMapping)->shouldReturn($expectedMapping);
    }

    public function it_normalizes_null_summary_as_empty_array(): void
    {
        $attributesMapping = new AttributeMappingCollection();
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
                    'code' => 'product_weight',
                    'label' => 'Product Weight',
                    'type' => 'metric',
                    'summary' => [],
                ],
                'attribute' => null,
                'status' => AttributeMappingStatus::ATTRIBUTE_PENDING,
                'exactMatchAttributeFromOtherFamily' => null,
                'canCreateAttribute' => true,
            ],
        ];

        $this->normalize($attributesMapping)->shouldReturn($expectedMapping);
    }
}
