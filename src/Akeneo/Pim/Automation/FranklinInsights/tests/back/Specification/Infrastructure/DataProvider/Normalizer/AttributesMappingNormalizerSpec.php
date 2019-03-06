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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Normalizer;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Write\AttributesMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Model\Read\Attribute;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\AttributeMapping;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Normalizer\AttributesMappingNormalizer;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class AttributesMappingNormalizerSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(AttributesMappingNormalizer::class);
    }

    public function it_normalizes_attributes_mapping_that_does_not_contain_attribute(): void
    {
        $attributesMapping = new AttributesMapping('router');
        $attributesMapping->map('label', 'text', null);

        $expectedData = [
            'from' => ['id' => 'label'],
            'to' => null,
            'status' => AttributeMapping::STATUS_PENDING,
        ];

        $this->normalize($attributesMapping)->shouldReturn([$expectedData]);
    }

    public function it_normalizes_attribute_mapping_mapped_to_attribute(): void
    {
        $attrColor = new Attribute(new AttributeCode('pim_color'), 1, AttributeTypes::OPTION_SIMPLE_SELECT, false, false, false, false, [], null, null);

        $attributesMapping = new AttributesMapping('router');
        $attributesMapping->map('color', 'select', $attrColor);

        $expectedData = [
            'from' => ['id' => 'color'],
            'to' => [
                'id' => 'pim_color',
                'label' => [],
                'type' => 'select',
            ],
            'status' => AttributeMapping::STATUS_ACTIVE,
        ];

        $this->normalize($attributesMapping)->shouldReturn([$expectedData]);
    }

    public function it_normalizes_metric_attributes_with_its_unit(): void
    {
        $attrWeight = new Attribute(new AttributeCode('pim_weight'), 1, AttributeTypes::METRIC, false, false, false, false, [], null, 'KILOGRAM');

        $attributesMapping = new AttributesMapping('router');
        $attributesMapping->map('weight', 'metric', $attrWeight);

        $expectedData = [
            'from' => ['id' => 'weight'],
            'to' => [
                'id' => 'pim_weight',
                'label' => [],
                'type' => 'metric',
                'unit' => 'KILOGRAM',
            ],
            'status' => AttributeMapping::STATUS_ACTIVE,
        ];

        $this->normalize($attributesMapping)->shouldReturn([$expectedData]);
    }

    public function it_normalizes_attribute_translations(): void
    {
        $attrName = new Attribute(
            new AttributeCode('pim_name'),
            1,
            AttributeTypes::TEXT,
            false,
            false,
            false,
            false,
            [
                'en_US' => 'Name',
                'fr_FR' => 'Nom',
            ],
            null,
            null
        );

        $attributesMapping = new AttributesMapping('router');
        $attributesMapping->map('name', 'text', $attrName);

        $expectedData = [
            'from' => ['id' => 'name'],
            'to' => [
                'id' => 'pim_name',
                'label' => [
                    'en_US' => 'Name',
                    'fr_FR' => 'Nom',
                ],
                'type' => 'text',
            ],
            'status' => AttributeMapping::STATUS_ACTIVE,
        ];

        $this->normalize($attributesMapping)->shouldReturn([$expectedData]);
    }
}
