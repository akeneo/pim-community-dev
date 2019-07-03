<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi;

use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompleteness;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeTranslation;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\ChannelTranslationInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductCompletenessCollectionNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        ChannelRepositoryInterface $channelRepository,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        IdentifiableObjectRepositoryInterface $localeRepository
    ) {
        $this->beConstructedWith(
            $normalizer,
            $channelRepository,
            $attributeRepository,
            $localeRepository
        );
    }

    function it_supports_iterables()
    {
        $this->supportsNormalization(Argument::any())->shouldReturn(false);
    }

    function it_normalizes_completenesses_and_indexes_them(
        $normalizer,
        $channelRepository,
        $localeRepository,
        $attributeRepository,
        AttributeInterface $attribute,
        AttributeTranslation $attributeTranslationFr,
        AttributeTranslation $attributeTranslationEn,
        ArrayCollection $completenessCollection,
        \ArrayIterator $completenessIterator,
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
        $completenessMobileEnUS = new ProductCompleteness('mobile', 'en_US', 2, ['name']);
        $completenessMobileFrFR = new ProductCompleteness('mobile', 'fr_FR', 2, ['name']);
        $completenessPrintEnUS = new ProductCompleteness('print', 'en_US', 2, ['name']);
        $completenessPrintFrFR = new ProductCompleteness('print', 'fr_FR', 2, ['name']);
        $completenessNumericEnUS = new ProductCompleteness('1234567890', 'en_US', 2, ['name']);
        $completenessNumericFrFR = new ProductCompleteness('1234567890', 'fr_FR', 2, ['name']);

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

        $channelRepository->findBy(['code' => ['mobile', 'print', '1234567890']])->willReturn([
            $mobile,
            $print,
            $numeric
        ]);
        $localeRepository->findOneByIdentifier('en_US')->willReturn($enUS);
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($frFR);
        $attributeRepository->findOneByIdentifier('name')->willReturn($attribute);

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
