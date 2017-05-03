<?php

namespace spec\Pim\Bundle\EnrichBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeTranslation;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\CompletenessInterface;
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
        CompletenessInterface $completeness,
        ChannelInterface $channel
    ) {
        $normalizer->normalize(Argument::any(), 'internal_api', ['a_context_key' => 'context_value'])
            ->willReturn(['missing' => [], 'completeness' => 'normalized_completeness']);

        $completeness->getChannel()->willReturn($channel);
        $channel->getCode()->willReturn('mobile', 'print', 'tablet', 'mobile', 'print', 'tablet');

        $attribute->getCode()->willReturn('name');

        $attribute->getTranslation('en_US')->willReturn($attributeTranslationEn);
        $attribute->getTranslation('fr_FR')->willReturn($attributeTranslationFr);

        $attributeTranslationEn->getLabel()->willReturn('labelEn');
        $attributeTranslationFr->getLabel()->willReturn('labelFr');

        $this->normalize([
            'en_US' => [
                'locale' => 'en_US',
                'stats' => [],
                'channels' => [
                    'mobile' => ['missing' => [$attribute], 'completeness' => $completeness],
                    'print'  => ['missing' => [$attribute], 'completeness' => $completeness],
                    'tablet' => ['missing' => [$attribute], 'completeness' => $completeness]
                ],
            ],
            'fr_FR' => [
                'locale' => 'fr_FR',
                'stats' => [],
                'channels' => [
                    'mobile' => ['missing' => [$attribute], 'completeness' => $completeness],
                    'print'  => ['missing' => [$attribute], 'completeness' => $completeness],
                    'tablet' => ['missing' => [$attribute], 'completeness' => $completeness]
                ]
            ]
        ], 'internal_api', ['a_context_key' => 'context_value'])
            ->shouldReturn(
                [
                    [
                        'locale'   => 'en_US',
                        'stats'    => [],
                        'channels' => [
                            'mobile' => [
                                'completeness' => [
                                  'missing' => [
                                  ],
                                  'completeness' => 'normalized_completeness',
                                ],
                                'missing' => [
                                  [
                                    'code' => 'name',
                                    'labels' => [
                                      'en_US' => 'labelEn',
                                      'fr_FR' => 'labelFr',
                                    ],
                                  ],
                                ],
                            ],
                            'print'  => [
                                'completeness' => [
                                  'missing' => [
                                  ],
                                  'completeness' => 'normalized_completeness',
                                ],
                                'missing' => [
                                  [
                                    'code' => 'name',
                                    'labels' => [
                                      'en_US' => 'labelEn',
                                      'fr_FR' => 'labelFr',
                                    ],
                                  ],
                                ],
                            ],
                            'tablet' => [
                                'completeness' => [
                                  'missing' => [
                                  ],
                                  'completeness' => 'normalized_completeness',
                                ],
                                'missing' => [
                                  [
                                    'code' => 'name',
                                    'labels' => [
                                      'en_US' => 'labelEn',
                                      'fr_FR' => 'labelFr',
                                    ],
                                  ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'locale'   => 'fr_FR',
                        'stats'    => [],
                        'channels' => [
                            'mobile' => [
                                'completeness' => [
                                  'missing' => [
                                  ],
                                  'completeness' => 'normalized_completeness',
                                ],
                                'missing' => [
                                  [
                                    'code' => 'name',
                                    'labels' => [
                                      'en_US' => 'labelEn',
                                      'fr_FR' => 'labelFr',
                                    ],
                                  ],
                                ],
                            ],
                            'print'  => [
                                'completeness' => [
                                  'missing' => [
                                  ],
                                  'completeness' => 'normalized_completeness',
                                ],
                                'missing' => [
                                  [
                                    'code' => 'name',
                                    'labels' => [
                                      'en_US' => 'labelEn',
                                      'fr_FR' => 'labelFr',
                                    ],
                                  ],
                                ],
                            ],
                            'tablet' => [
                                'completeness' => [
                                  'missing' => [
                                  ],
                                  'completeness' => 'normalized_completeness',
                                ],
                                'missing' => [
                                  [
                                    'code' => 'name',
                                    'labels' => [
                                      'en_US' => 'labelEn',
                                      'fr_FR' => 'labelFr',
                                    ],
                                  ],
                                ]
                            ],
                        ],
                    ],
                ]
            );
    }
}
