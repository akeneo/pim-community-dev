<?php

namespace spec\Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;

class CollectionNormalizerSpec extends ObjectBehavior
{

    function it_is_a_serializer_aware_normalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_flat_normalization_of_collection()
    {
        $collection = ['data' => ['value1', 'value2']];

        $this->supportsNormalization($collection, 'flat')->shouldBe(true);
        $this->supportsNormalization($collection, 'csv')->shouldBe(false);
        $this->supportsNormalization(1, 'csv')->shouldBe(false);
    }

    function it_normalizes_a_simple_multiselect()
    {
        $standardCollectionProductValue = [
            "a_multi_select" => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => [
                        'optionA',
                        'optionB',
                    ],
                ],
            ],
        ];

        $this->normalize($standardCollectionProductValue)->shouldReturn(
            [
                'a_multi_select' => 'optionA,optionB',
            ]
        );
    }

    function it_normalizes_a_localizable_multiselect()
    {
        $standardCollectionProductValue = [
            "a_multi_select" => [
                [
                    'locale' => 'en_US',
                    'scope'  => null,
                    'data'   => [
                        'a_optionA',
                        'a_optionB',
                    ],
                ],
                [
                    'locale' => 'fr_FR',
                    'scope'  => null,
                    'data'   => [
                        'une_optionA',
                        'une_optionB',
                    ],
                ],
            ],
        ];

        $this->normalize($standardCollectionProductValue)->shouldReturn(
            [
                'a_multi_select-en_US' => 'a_optionA,a_optionB',
                'a_multi_select-fr_FR' => 'une_optionA,une_optionB',
            ]
        );
    }

    function it_normalizes_a_scopable_multiselect()
    {
        $standardCollectionProductValue = [
            "a_multi_select" => [
                [
                    'locale' => null,
                    'scope'  => 'mobile',
                    'data'   => [
                        'optA',
                        'optB',
                    ],
                ],
                [
                    'locale' => null,
                    'scope'  => 'ecommerce',
                    'data'   => [
                        'optionA',
                        'optionB',
                    ],
                ],
            ],
        ];

        $this->normalize($standardCollectionProductValue)->shouldReturn(
            [
                'a_multi_select-mobile'    => 'optA,optB',
                'a_multi_select-ecommerce' => 'optionA,optionB',
            ]
        );
    }

    function it_normalizes_a_scopable_and_localizable_multiselect()
    {
        $standardCollectionProductValue = [
            "a_multi_select" => [
                [
                    'locale' => 'fr_FR',
                    'scope'  => 'mobile',
                    'data'   => [
                        'optA',
                        'optB',
                    ],
                ],
                [
                    'locale' => 'en_US',
                    'scope'  => 'ecommerce',
                    'data'   => [
                        'a_optionA',
                        'a_optionB',
                    ],
                ],
            ],
        ];

        $this->normalize($standardCollectionProductValue)->shouldReturn(
            [
                'a_multi_select-fr_FR-mobile'    => 'optA,optB',
                'a_multi_select-en_US-ecommerce' => 'a_optionA,a_optionB',
            ]
        );
    }
}
