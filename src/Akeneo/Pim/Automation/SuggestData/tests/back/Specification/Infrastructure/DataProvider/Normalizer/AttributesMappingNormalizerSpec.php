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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Normalizer;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\Write\AttributeMapping;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Normalizer\AttributesMappingNormalizer;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 */
class AttributesMappingNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(AttributesMappingNormalizer::class);
    }

    function it_normalizes_attributes_mapping_that_does_not_contain_attribute(AttributeMapping $attributeMapping)
    {
        $attributeMapping->getTargetAttributeCode()->willReturn('target_attr');
        $attributeMapping->getStatus()->willReturn(AttributeMapping::ATTRIBUTE_UNMAPPED);
        $attributeMapping->getAttribute()->willReturn(null);

        $expectedData = [
            'from' => ['id' => 'target_attr'],
            'to' => null,
            'status' => AttributeMapping::ATTRIBUTE_UNMAPPED
        ];

        $this->normalize([$attributeMapping])->shouldReturn([$expectedData]);
    }

    function it_normalizes_attributes_mapping_that_contains_attribute(
        AttributeMapping $attributeMapping,
        AttributeInterface $attribute
    ) {
        $attributeMapping->getTargetAttributeCode()->willReturn('target_attr');
        $attributeMapping->getStatus()->willReturn(AttributeMapping::ATTRIBUTE_MAPPED);
        $attributeMapping->getAttribute()->willReturn($attribute);

        $attribute->getCode()->willReturn('pim_attr');
        $attribute->getLabel()->willReturn('Pim Attribute');
        $attribute->setLocale('en_US')->willReturn();

        $expectedData = [
            'from' => ['id' => 'target_attr'],
            'to' => [
                'id' => 'pim_attr',
                'label' => ['en_US' => 'Pim Attribute'],
                'type' => 'text'
            ],
            'status' => AttributeMapping::ATTRIBUTE_MAPPED
        ];

        $this->normalize([$attributeMapping])->shouldReturn([$expectedData]);
    }
}
