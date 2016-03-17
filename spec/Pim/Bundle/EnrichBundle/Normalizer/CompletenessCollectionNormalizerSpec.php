<?php

namespace spec\Pim\Bundle\EnrichBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeTranslation;
use Pim\Component\Catalog\Model\AttributeInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CompletenessCollectionNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $normalizer) {
        $this->beConstructedWith($normalizer);
    }

    function it_supports_iterables() {
        $this->supportsNormalization(Argument::any())->shouldReturn(false);
    }

    function it_normalizes_completenesses_and_indexes_them (
        $normalizer,
        AttributeInterface $attribute,
        AttributeTranslation $attributeTranslationFr,
        AttributeTranslation $attributeTranslationEn
    ) {
        $normalizer->normalize('completeness', 'internal_api', ['a_context_key' => 'context_value'])
            ->willReturn('normalized_completeness');

        $attribute->getCode()->willReturn('name');

        $attribute->getTranslation('en_US')->willReturn($attributeTranslationEn);
        $attribute->getTranslation('fr_FR')->willReturn($attributeTranslationFr);

        $attributeTranslationEn->getLabel()->willReturn('labelEn');
        $attributeTranslationFr->getLabel()->willReturn('labelFr');

        $this->normalize([
            'en_US' => [
                'channels' => [
                    'mobile' => ['missing' => [$attribute], 'completeness' => 'completeness'],
                    'print'  => ['missing' => [$attribute], 'completeness' => 'completeness'],
                    'tablet' => ['missing' => [$attribute], 'completeness' => 'completeness']
                ],
            ],
            'fr_FR' => [
                'channels' => [
                    'mobile' => ['missing' => [$attribute], 'completeness' => 'completeness'],
                    'print'  => ['missing' => [$attribute], 'completeness' => 'completeness'],
                    'tablet' => ['missing' => [$attribute], 'completeness' => 'completeness']
                ]
            ]
        ], 'internal_api', ['a_context_key' => 'context_value'])
            ->shouldReturn([
                'en_US' => [
                    'channels' => [
                        'mobile' => [
                            'missing'      => [
                                ['code' => 'name', 'labels' => ['en_US' => 'labelEn', 'fr_FR' => 'labelFr']],
                            ],
                            'completeness' => 'normalized_completeness'
                        ],
                        'print'  => [
                            'missing'      => [
                                ['code' => 'name', 'labels' => ['en_US' => 'labelEn', 'fr_FR' => 'labelFr']],
                            ],
                            'completeness' => 'normalized_completeness'
                        ],
                        'tablet' => [
                            'missing'      => [
                                ['code' => 'name', 'labels' => ['en_US' => 'labelEn', 'fr_FR' => 'labelFr']],
                            ],
                            'completeness' => 'normalized_completeness'
                        ]
                    ],
                ],
                'fr_FR' => [
                    'channels' => [
                        'mobile' => [
                            'missing'      => [
                                ['code' => 'name', 'labels' => ['en_US' => 'labelEn', 'fr_FR' => 'labelFr']],
                            ],
                            'completeness' => 'normalized_completeness'
                        ],
                        'print'  => [
                            'missing'      => [
                                ['code' => 'name', 'labels' => ['en_US' => 'labelEn', 'fr_FR' => 'labelFr']],
                            ],
                            'completeness' => 'normalized_completeness'
                        ],
                        'tablet' => [
                            'missing'      => [
                                ['code' => 'name', 'labels' => ['en_US' => 'labelEn', 'fr_FR' => 'labelFr']],
                            ],
                            'completeness' => 'normalized_completeness'
                        ]
                    ]
                ]
            ]);
    }
}
