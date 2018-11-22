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

use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Model\Write\AttributeMapping as DomainAttributeMapping;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\ValueObject\AttributeMapping;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Normalizer\AttributesMappingNormalizer;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeTranslationInterface;
use Doctrine\Common\Collections\ArrayCollection;
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

    public function it_normalizes_attributes_mapping_that_does_not_contain_attribute(
        DomainAttributeMapping $attributeMapping
    ): void {
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
        DomainAttributeMapping $colorMapping,
        DomainAttributeMapping $sizeMapping,
        DomainAttributeMapping $heightMapping,
        AttributeTranslationInterface $enTranslation,
        AttributeTranslationInterface $frTranslation,
        ArrayCollection $colorTranslations,
        ArrayCollection $sizeTranslations,
        ArrayCollection $heightTranslations,
        AttributeInterface $attributeColor,
        AttributeInterface $attributeSize,
        AttributeInterface $attributeHeight,
        \ArrayIterator $translationsIterator
    ): void {
        $colorMapping->getTargetAttributeCode()->willReturn('color_target');
        $colorMapping->getStatus()->willReturn(DomainAttributeMapping::ATTRIBUTE_PENDING);
        $colorMapping->getAttribute()->willReturn($attributeColor);

        $sizeMapping->getTargetAttributeCode()->willReturn('size_target');
        $sizeMapping->getStatus()->willReturn(DomainAttributeMapping::ATTRIBUTE_MAPPED);
        $sizeMapping->getAttribute()->willReturn($attributeSize);

        $heightMapping->getTargetAttributeCode()->willReturn('height_target');
        $heightMapping->getStatus()->willReturn(DomainAttributeMapping::ATTRIBUTE_UNMAPPED);
        $heightMapping->getAttribute()->willReturn($attributeHeight);

        $attributeColor->getCode()->willReturn('color');
        $attributeColor->getTranslations()->willReturn($colorTranslations);
        $attributeColor->getType()->willReturn(AttributeTypes::OPTION_MULTI_SELECT);

        $attributeSize->getCode()->willReturn('size');
        $attributeSize->getTranslations()->willReturn($sizeTranslations);
        $attributeSize->getType()->willReturn(AttributeTypes::METRIC);
        $attributeSize->getDefaultMetricUnit()->willReturn('CENTIMETER');

        $attributeHeight->getCode()->willReturn('height');
        $attributeHeight->getTranslations()->willReturn($heightTranslations);
        $attributeHeight->getType()->willReturn(AttributeTypes::METRIC);
        $attributeHeight->getDefaultMetricUnit()->willReturn('CENTIMETER');

        $sizeTranslations->isEmpty()->willReturn(true);
        $colorTranslations->isEmpty()->willReturn(false);
        $heightTranslations->isEmpty()->willReturn(true);

        $colorTranslations->getIterator()->willReturn($translationsIterator);
        $translationsIterator->valid()->willReturn(true, true, false);
        $translationsIterator->current()->willReturn($frTranslation, $enTranslation);
        $translationsIterator->next()->shouldBeCalled();
        $translationsIterator->rewind()->shouldBeCalled();

        $frTranslation->getLocale()->willReturn('fr_FR');
        $frTranslation->getLabel()->willReturn('Couleur');

        $enTranslation->getLocale()->willReturn('en_US');
        $enTranslation->getLabel()->willReturn('Color');

        $expectedData = [
            [
                'from' => ['id' => 'color_target'],
                'to' => [
                    'id' => 'color',
                    'label' => [
                        'fr_FR' => 'Couleur',
                        'en_US' => 'Color',
                    ],
                    'type' => 'multiselect',
                ],
                'status' => AttributeMapping::STATUS_PENDING,
            ],
            [
                'from' => ['id' => 'size_target'],
                'to' => [
                    'id' => 'size',
                    'label' => [],
                    'type' => 'metric',
                    'unit' => 'CENTIMETER',
                ],
                'status' => AttributeMapping::STATUS_ACTIVE,
            ],
            [
                'from' => ['id' => 'height_target'],
                'to' => [
                    'id' => 'height',
                    'label' => [],
                    'type' => 'metric',
                    'unit' => 'CENTIMETER',
                ],
                'status' => AttributeMapping::STATUS_INACTIVE,
            ],
        ];

        $this->normalize([$colorMapping, $sizeMapping, $heightMapping])->shouldReturn($expectedData);
    }
}
