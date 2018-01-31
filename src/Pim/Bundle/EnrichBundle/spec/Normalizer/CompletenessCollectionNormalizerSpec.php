<?php

namespace spec\Pim\Bundle\EnrichBundle\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeTranslation;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\ChannelTranslationInterface;
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
        ArrayCollection $attributeCollectionNumericEnUS,
        ArrayCollection $attributeCollectionNumericFrFR,
        \ArrayIterator $attributeIteratorMobileEnUS,
        \ArrayIterator $attributeIteratorMobileFrFR,
        \ArrayIterator $attributeIteratorPrintEnUS,
        \ArrayIterator $attributeIteratorPrintFrFR,
        \ArrayIterator $attributeIteratorNumericEnUS,
        \ArrayIterator $attributeIteratorNumericFrFR,
        CompletenessInterface $completenessMobileEnUS,
        CompletenessInterface $completenessMobileFrFR,
        CompletenessInterface $completenessPrintEnUS,
        CompletenessInterface $completenessPrintFrFR,
        CompletenessInterface $completenessNumericEnUS,
        CompletenessInterface $completenessNumericFrFR,
        ChannelInterface $mobile,
        ChannelInterface $print,
        ChannelInterface $numeric,
        LocaleInterface $enUS,
        LocaleInterface $frFR,
        ChannelTranslationInterface $translationMobileEn,
        ChannelTranslationInterface $translationMobileFr,
        ChannelTranslationInterface $translationPrintEn,
        ChannelTranslationInterface $translationPrintFr,
        ChannelTranslationInterface $translationNumericEn,
        ChannelTranslationInterface $translationNumericFr
    ) {
        $completenessCollection->getIterator()->willReturn($completenessIterator);
        $completenessIterator->rewind()->shouldBeCalled();
        $completenessIterator->valid()->willReturn(true, true, true, true, true, true, false);
        $completenessIterator->current()->willReturn(
            $completenessMobileEnUS,
            $completenessMobileFrFR,
            $completenessPrintEnUS,
            $completenessPrintFrFR,
            $completenessNumericEnUS,
            $completenessNumericFrFR
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

        $attributeCollectionNumericEnUS->getIterator()->willReturn($attributeIteratorNumericEnUS);
        $attributeIteratorNumericEnUS->rewind()->shouldBeCalled();
        $attributeIteratorNumericEnUS->valid()->willReturn(true, false);
        $attributeIteratorNumericEnUS->current()->willReturn($attribute);
        $attributeIteratorNumericEnUS->next()->shouldBeCalled();

        $attributeCollectionNumericFrFR->getIterator()->willReturn($attributeIteratorNumericFrFR);
        $attributeIteratorNumericFrFR->rewind()->shouldBeCalled();
        $attributeIteratorNumericFrFR->valid()->willReturn(true, false);
        $attributeIteratorNumericFrFR->current()->willReturn($attribute);
        $attributeIteratorNumericFrFR->next()->shouldBeCalled();

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

        $completenessNumericEnUS->getChannel()->willReturn($numeric);
        $completenessNumericEnUS->getLocale()->willReturn($enUS);
        $completenessNumericEnUS->getRatio()->willReturn(50);
        $completenessNumericEnUS->getRequiredCount()->willReturn(2);
        $completenessNumericEnUS->getMissingCount()->willReturn(1);
        $completenessNumericEnUS->getMissingAttributes()->willReturn($attributeCollectionNumericEnUS);

        $completenessNumericFrFR->getChannel()->willReturn($numeric);
        $completenessNumericFrFR->getLocale()->willReturn($frFR);
        $completenessNumericFrFR->getRatio()->willReturn(50);
        $completenessNumericFrFR->getRequiredCount()->willReturn(2);
        $completenessNumericFrFR->getMissingCount()->willReturn(1);
        $completenessNumericFrFR->getMissingAttributes()->willReturn($attributeCollectionNumericFrFR);

        $mobile->getCode()->willReturn('mobile');
        $print->getCode()->willReturn('print');
        $numeric->getCode()->willReturn("1234567890");
        $enUS->getCode()->willReturn('en_US');
        $frFR->getCode()->willReturn('fr_FR');
        $enUS->getName()->willReturn('English');
        $frFR->getName()->willReturn('French');
        $mobile->getTranslation('en_US')->willReturn($translationMobileEn);
        $mobile->getTranslation('fr_FR')->willReturn($translationMobileFr);
        $print->getTranslation('en_US')->willReturn($translationPrintEn);
        $print->getTranslation('fr_FR')->willReturn($translationPrintFr);
        $numeric->getTranslation('en_US')->willReturn($translationNumericEn);
        $numeric->getTranslation('fr_FR')->willReturn($translationNumericFr);
        $translationMobileEn->getLabel()->willReturn('mobile');
        $translationMobileFr->getLabel()->willReturn('mobile');
        $translationPrintEn->getLabel()->willReturn('print');
        $translationPrintFr->getLabel()->willReturn('impression');
        $translationNumericEn->getLabel()->willReturn("1234567890");
        $translationNumericFr->getLabel()->willReturn("1234567890");

        $attribute->getCode()->willReturn('name');
        $attribute->getTranslation('en_US')->willReturn($attributeTranslationEn);
        $attribute->getTranslation('fr_FR')->willReturn($attributeTranslationFr);
        $attributeTranslationEn->getLabel()->willReturn('Name');
        $attributeTranslationFr->getLabel()->willReturn('Nom');

        $normalizer->normalize(Argument::cetera())->shouldBeCalledTimes(6);
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
        $normalizer
            ->normalize($completenessNumericEnUS, 'internal_api', ['a_context_key' => 'context_value'])
            ->willReturn([]);
        $normalizer
            ->normalize($completenessNumericFrFR, 'internal_api', ['a_context_key' => 'context_value'])
            ->willReturn([]);

        $this
            ->normalize($completenessCollection, 'internal_api', ['a_context_key' => 'context_value'])
            ->shouldReturn([
            [
                'channel' => "mobile",
                'labels'  => [
                    'en_US' => "mobile",
                    'fr_FR' => "mobile",
                ],
                'stats'   => [
                    'total'    => 2,
                    'complete' => 0,
                    'average'  => 50,
                ],
                'locales' => [
                    'en_US' => [
                        'completeness' => [],
                        'missing'      => [
                            [
                                'code'   => "name",
                                'labels' => [
                                    'en_US' => 'Name',
                                    'fr_FR' => 'Nom'
                                ]
                            ],
                        ],
                        'label'        => "English",
                    ],
                    'fr_FR' => [
                        'completeness' => [],
                        'missing'      => [
                            [
                                'code'  => "name",
                                'labels' => [
                                    'en_US' => 'Name',
                                    'fr_FR' => 'Nom'
                                ]
                            ],
                        ],
                        'label'        => "French",
                    ],
                ],
            ], [
                'channel' => "print",
                'labels'  => [
                    'en_US' => "print",
                    'fr_FR' => "impression",
                ],
                'stats'   => [
                    'total'    => 2,
                    'complete' => 0,
                    'average'  => 50,
                ],
                'locales' => [
                    'en_US' => [
                        'completeness' => [],
                        'missing'      => [
                            [
                                'code'  => "name",
                                'labels' => [
                                    'en_US' => 'Name',
                                    'fr_FR' => 'Nom'
                                ]
                            ],
                        ],
                        'label'        => "English",
                    ],
                    'fr_FR' => [
                        'completeness' => [],
                        'missing'      => [
                            [
                                'code'  => "name",
                                'labels' => [
                                    'en_US' => 'Name',
                                    'fr_FR' => 'Nom'
                                ]
                            ],
                        ],
                        'label'        => "French",
                    ]
                ]
            ], [
                    'channel' => "1234567890",
                    'labels'  => [
                        'en_US' => "1234567890",
                        'fr_FR' => "1234567890",
                    ],
                    'stats'   => [
                        'total'    => 2,
                        'complete' => 0,
                        'average'  => 50,
                    ],
                    'locales' => [
                        'en_US' => [
                            'completeness' => [],
                            'missing'      => [
                                [
                                    'code'   => "name",
                                    'labels' => [
                                        'en_US' => 'Name',
                                        'fr_FR' => 'Nom'
                                    ]
                                ],
                            ],
                            'label'        => "English",
                        ],
                        'fr_FR' => [
                            'completeness' => [],
                            'missing'      => [
                                [
                                    'code'  => "name",
                                    'labels' => [
                                        'en_US' => 'Name',
                                        'fr_FR' => 'Nom'
                                    ]
                                ],
                            ],
                            'label'        => "French",
                        ],
                    ],
                ]
        ]);
    }
}
