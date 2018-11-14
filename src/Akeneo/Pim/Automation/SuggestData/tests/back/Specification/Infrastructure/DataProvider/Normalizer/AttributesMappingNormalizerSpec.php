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
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\ValueObject\AttributeMapping;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Normalizer\AttributesMappingNormalizer;
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
        AttributeTranslationInterface $enTranslation,
        AttributeTranslationInterface $frTranslation,
        ArrayCollection $colorTranslations,
        ArrayCollection $sizeTranslations,
        AttributeInterface $attributeColor,
        AttributeInterface $attributeSize,
        \ArrayIterator $translationsIterator
    ): void {
        $colorMapping->getTargetAttributeCode()->willReturn('color_target');
        $colorMapping->getStatus()->willReturn(DomainAttributeMapping::ATTRIBUTE_MAPPED);
        $colorMapping->getAttribute()->willReturn($attributeColor);

        $sizeMapping->getTargetAttributeCode()->willReturn('size_target');
        $sizeMapping->getStatus()->willReturn(DomainAttributeMapping::ATTRIBUTE_MAPPED);
        $sizeMapping->getAttribute()->willReturn($attributeSize);

        $attributeColor->getCode()->willReturn('color');
        $attributeColor->getTranslations()->willReturn($colorTranslations);

        $attributeSize->getCode()->willReturn('size');
        $attributeSize->getTranslations()->willReturn($sizeTranslations);

        $sizeTranslations->isEmpty()->willReturn(true);
        $colorTranslations->isEmpty()->willReturn(false);

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
                    'type' => 'text',
                ],
                'status' => AttributeMapping::STATUS_ACTIVE,
            ],
            [
                'from' => ['id' => 'size_target'],
                'to' => [
                    'id' => 'size',
                    'label' => [],
                    'type' => 'text',
                ],
                'status' => AttributeMapping::STATUS_ACTIVE,
            ],
        ];

        $this->normalize([$colorMapping, $sizeMapping])->shouldReturn($expectedData);
    }
}
