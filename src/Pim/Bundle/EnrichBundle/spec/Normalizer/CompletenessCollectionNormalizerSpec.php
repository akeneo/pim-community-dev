<?php

namespace spec\Pim\Bundle\EnrichBundle\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeTranslation;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\CompletenessInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CompletenessCollectionNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $normalizer)
    {
        $this->beConstructedWith($normalizer);
    }

    function it_supports_iterables()
    {
        $this->supportsNormalization(Argument::any())->shouldReturn(false);
    }

    function it_normalizes_completenesses_and_indexes_them(
        $normalizer,
        AttributeInterface $attribute,
        AttributeTranslation $attributeTranslationFr,
        AttributeTranslation $attributeTranslationEn,
        ArrayCollection $completenessCollection,
        \ArrayIterator $completenessIterator,
        ArrayCollection $attributeCollectionMobileEnUS,
        ArrayCollection $attributeCollectionMobileFrFR,
        ArrayCollection $attributeCollectionPrintEnUS,
        ArrayCollection $attributeCollectionPrintFrFR,
        \ArrayIterator $attributeIteratorMobileEnUS,
        \ArrayIterator $attributeIteratorMobileFrFR,
        \ArrayIterator $attributeIteratorPrintEnUS,
        \ArrayIterator $attributeIteratorPrintFrFR,
        CompletenessInterface $completenessMobileEnUS,
        CompletenessInterface $completenessMobileFrFR,
        CompletenessInterface $completenessPrintEnUS,
        CompletenessInterface $completenessPrintFrFR,
        ChannelInterface $mobile,
        ChannelInterface $print,
        LocaleInterface $enUS,
        LocaleInterface $frFR
    ) {
        $completenessCollection->getIterator()->willReturn($completenessIterator);
        $completenessIterator->rewind()->shouldBeCalled();
        $completenessIterator->valid()->willReturn(true, true, true, true, false);
        $completenessIterator->current()->willReturn(
            $completenessMobileEnUS,
            $completenessMobileFrFR,
            $completenessPrintEnUS,
            $completenessPrintFrFR
        );
        $completenessIterator->next()->shouldBeCalled();

        $attributeCollectionMobileEnUS->getIterator()->willReturn($attributeIteratorMobileEnUS);
        $attributeIteratorMobileEnUS->rewind()->shouldBeCalled();
        $attributeIteratorMobileEnUS->valid()->willReturn(true, false);
        $attributeIteratorMobileEnUS->current()->willReturn($attribute);
        $attributeIteratorMobileEnUS->next()->shouldBeCalled();

        $attributeCollectionMobileFrFR->getIterator()->willReturn($attributeIteratorMobileFrFR);
        $attributeIteratorMobileFrFR->rewind()->shouldBeCalled();
        $attributeIteratorMobileFrFR->valid()->willReturn(true, false);
        $attributeIteratorMobileFrFR->current()->willReturn($attribute);
        $attributeIteratorMobileFrFR->next()->shouldBeCalled();

        $attributeCollectionPrintEnUS->getIterator()->willReturn($attributeIteratorPrintEnUS);
        $attributeIteratorPrintEnUS->rewind()->shouldBeCalled();
        $attributeIteratorPrintEnUS->valid()->willReturn(true, false);
        $attributeIteratorPrintEnUS->current()->willReturn($attribute);
        $attributeIteratorPrintEnUS->next()->shouldBeCalled();

        $attributeCollectionPrintFrFR->getIterator()->willReturn($attributeIteratorPrintFrFR);
        $attributeIteratorPrintFrFR->rewind()->shouldBeCalled();
        $attributeIteratorPrintFrFR->valid()->willReturn(true, false);
        $attributeIteratorPrintFrFR->current()->willReturn($attribute);
        $attributeIteratorPrintFrFR->next()->shouldBeCalled();

        $mobile->getCode()->willReturn('mobile');
        $print->getCode()->willReturn('print');
        $enUS->getCode()->willReturn('en_US');
        $frFR->getCode()->willReturn('fr_FR');

        $completenessMobileEnUS->getChannel()->willReturn($mobile);
        $completenessMobileEnUS->getLocale()->willReturn($enUS);
        $completenessMobileEnUS->getRatio()->willReturn(50);
        $completenessMobileEnUS->getRequiredCount()->willReturn(2);
        $completenessMobileEnUS->getMissingCount()->willReturn(1);
        $completenessMobileEnUS->getMissingAttributes()->willReturn($attributeCollectionMobileEnUS);

        $completenessMobileFrFR->getChannel()->willReturn($mobile);
        $completenessMobileFrFR->getLocale()->willReturn($frFR);
        $completenessMobileFrFR->getRatio()->willReturn(50);
        $completenessMobileFrFR->getRequiredCount()->willReturn(2);
        $completenessMobileFrFR->getMissingCount()->willReturn(1);
        $completenessMobileFrFR->getMissingAttributes()->willReturn($attributeCollectionMobileFrFR);

        $completenessPrintEnUS->getChannel()->willReturn($print);
        $completenessPrintEnUS->getLocale()->willReturn($enUS);
        $completenessPrintEnUS->getRatio()->willReturn(50);
        $completenessPrintEnUS->getRequiredCount()->willReturn(2);
        $completenessPrintEnUS->getMissingCount()->willReturn(1);
        $completenessPrintEnUS->getMissingAttributes()->willReturn($attributeCollectionPrintEnUS);

        $completenessPrintFrFR->getChannel()->willReturn($print);
        $completenessPrintFrFR->getLocale()->willReturn($frFR);
        $completenessPrintFrFR->getRatio()->willReturn(50);
        $completenessPrintFrFR->getRequiredCount()->willReturn(2);
        $completenessPrintFrFR->getMissingCount()->willReturn(1);
        $completenessPrintFrFR->getMissingAttributes()->willReturn($attributeCollectionPrintFrFR);

        $attribute->getCode()->willReturn('name');

        $attribute->getTranslation('en_US')->willReturn($attributeTranslationEn);
        $attribute->getTranslation('fr_FR')->willReturn($attributeTranslationFr);

        $attributeTranslationEn->getLabel()->willReturn('Name');
        $attributeTranslationFr->getLabel()->willReturn('Nom');

        $normalizer->normalize(Argument::cetera())->shouldBeCalledTimes(4);
        $normalizer
            ->normalize($completenessMobileEnUS, 'internal_api', ['a_context_key' => 'context_value'])
            ->willReturn([]);
        $normalizer
            ->normalize($completenessMobileFrFR, 'internal_api', ['a_context_key' => 'context_value'])
            ->willReturn([]);
        $normalizer
            ->normalize($completenessPrintEnUS, 'internal_api', ['a_context_key' => 'context_value'])
            ->willReturn([]);
        $normalizer
            ->normalize($completenessPrintFrFR, 'internal_api', ['a_context_key' => 'context_value'])
            ->willReturn([]);

        $this
            ->normalize($completenessCollection, 'internal_api', ['a_context_key' => 'context_value'])
            ->shouldReturn(
                [
                    [
                        'locale'   => 'en_US',
                        'stats'    => [
                            'total'    => 2,
                            'complete' => 0,
                        ],
                        'channels' => [
                            'mobile' => [
                                'completeness' => [],
                                'missing' => [
                                    ['code' => 'name', 'labels' => ['en_US' => 'Name', 'fr_FR' => 'Nom']],
                                ],
                            ],
                            'print'  => [
                                'completeness' => [],
                                'missing' => [
                                    ['code' => 'name', 'labels' => ['en_US' => 'Name', 'fr_FR' => 'Nom']],
                                ],
                            ],
                        ],
                    ],
                    [
                        'locale'   => 'fr_FR',
                        'stats'    => [
                            'total'    => 2,
                            'complete' => 0,
                        ],
                        'channels' => [
                            'mobile' => [
                                'completeness' => [],
                                'missing' => [
                                    ['code' => 'name', 'labels' => ['en_US' => 'Name', 'fr_FR' => 'Nom']],
                                ],
                            ],
                            'print'  => [
                                'completeness' => [],
                                'missing' => [
                                    ['code' => 'name', 'labels' => ['en_US' => 'Name', 'fr_FR' => 'Nom']],
                                ],
                            ],
                        ],
                    ],
                ]
            );
    }
}
