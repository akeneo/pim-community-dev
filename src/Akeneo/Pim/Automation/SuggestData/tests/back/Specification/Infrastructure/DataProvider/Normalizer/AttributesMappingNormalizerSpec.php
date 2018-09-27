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

use Akeneo\Pim\Automation\SuggestData\Domain\Model\Write\AttributeMapping as DomainAttributeMapping;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject\AttributeMapping;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Normalizer\AttributesMappingNormalizer;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 */
class AttributesMappingNormalizerSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(AttributesMappingNormalizer::class);
    }

    public function it_normalizes_attributes_mapping_that_does_not_contain_attribute(DomainAttributeMapping $attributeMapping): void
    {
        $attributeMapping->getTargetAttributeCode()->willReturn('target_attr');
        $attributeMapping->getStatus()->willReturn(DomainAttributeMapping::ATTRIBUTE_UNMAPPED);
        $attributeMapping->getAttribute()->willReturn(null);

        $expectedData = [
            'from' => ['id' => 'target_attr'],
            'to' => null,
            'status' => AttributeMapping::STATUS_INACTIVE,
        ];

        $this->normalize([$attributeMapping])->shouldReturn([$expectedData]);
    }

    public function it_normalizes_attributes_mapping_that_contains_attribute(
        DomainAttributeMapping $attributeMapping,
        AttributeInterface $attribute
    ): void {
        $attributeMapping->getTargetAttributeCode()->willReturn('target_attr');
        $attributeMapping->getStatus()->willReturn(DomainAttributeMapping::ATTRIBUTE_MAPPED);
        $attributeMapping->getAttribute()->willReturn($attribute);

        $attribute->getCode()->willReturn('pim_attr');
        $attribute->getLabel()->willReturn('Pim Attribute');
        $attribute->setLocale('en_US')->willReturn();

        $expectedData = [
            'from' => ['id' => 'target_attr'],
            'to' => [
                'id' => 'pim_attr',
                'label' => ['en_US' => 'Pim Attribute'],
                'type' => 'text',
            ],
            'status' => AttributeMapping::STATUS_ACTIVE,
        ];

        $this->normalize([$attributeMapping])->shouldReturn([$expectedData]);
    }
}
