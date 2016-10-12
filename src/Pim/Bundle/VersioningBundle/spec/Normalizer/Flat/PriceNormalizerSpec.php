<?php

namespace spec\Pim\Bundle\VersioningBundle\Normalizer\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductPriceInterface;

class PriceNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_flat_normalization_of_product_price(ProductPriceInterface $price)
    {
        $this->supportsNormalization($price, 'flat')->shouldBe(true);
        $this->supportsNormalization($price, 'csv')->shouldBe(false);
        $this->supportsNormalization(1, 'csv')->shouldBe(false);
    }

    function it_normalizes_a_price_standard_product_value()
    {
        $standardPriceProductValue = [
            "a_price" => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => [
                        [
                            'amount'   => '25.30',
                            'currency' => 'GB',
                        ],
                    ],
                ],
            ],
        ];

        $this->normalize($standardPriceProductValue, 'flat', [])->shouldReturn(
            [
                'a_price-GB' => '25.30',
            ]
        );
    }

    function it_normalizes_empty_standard_price_product_value()
    {
        $standardPriceProductValue = [
            "a_price" => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => [
                        [
                            'amount'   => '',
                            'currency' => 'GB',
                        ],
                    ],
                ],
            ],
        ];

        $this->normalize($standardPriceProductValue, 'flat', [])->shouldReturn(
            [
                'a_price-GB' => '',
            ]
        );
    }

    function it_normalizes_localizable_standard_prices()
    {
        $standardPriceProductValue = [
            "a_price" => [
                [
                    'locale' => 'en_US',
                    'scope'  => null,
                    'data'   => [
                        [
                            'amount'   => '25.30',
                            'currency' => 'GB',
                        ],
                        [
                            'amount'   => '15.30',
                            'currency' => 'EUR',
                        ],
                    ],
                ],
                [
                    'locale' => 'fr_FR',
                    'scope'  => null,
                    'data'   => [
                        [
                            'amount'   => '25.30',
                            'currency' => 'GB',
                        ],
                        [
                            'amount'   => '12.30',
                            'currency' => 'EUR',
                        ],
                    ],
                ],
            ],
        ];

        $this->normalize($standardPriceProductValue, 'flat', [])->shouldReturn(
            [
                'a_price-GB-en_US'  => '25.30',
                'a_price-EUR-en_US' => '15.30',
                'a_price-GB-fr_FR'  => '25.30',
                'a_price-EUR-fr_FR' => '12.30',
            ]
        );
    }

    function it_normalizes_scopable_standard_prices()
    {
        $standardPriceProductValue = [
            "a_price" => [
                [
                    'locale' => null,
                    'scope'  => 'ecommerce',
                    'data'   => [
                        [
                            'amount'   => '25.30',
                            'currency' => 'GB',
                        ],
                        [
                            'amount'   => '10.00',
                            'currency' => 'EUR',
                        ],
                    ],
                ],
                [
                    'locale' => null,
                    'scope'  => 'mobile',
                    'data'   => [
                        [
                            'amount'   => '25.30',
                            'currency' => 'GB',
                        ],
                        [
                            'amount'   => '12.30',
                            'currency' => 'EUR',
                        ],
                    ],
                ],
            ],
        ];

        $this->normalize($standardPriceProductValue, 'flat', [])->shouldReturn(
            [
                'a_price-GB-ecommerce'  => '25.30',
                'a_price-EUR-ecommerce' => '10.00',
                'a_price-GB-mobile'     => '25.30',
                'a_price-EUR-mobile'    => '12.30',
            ]
        );
    }

    function it_normalizes_scopable_and_localizable_standard_prices()
    {
        $standardPriceProductValue = [
            "a_price" => [
                [
                    'locale' => 'fr_FR',
                    'scope'  => 'ecommerce',
                    'data'   => [
                        [
                            'amount'   => '25.30',
                            'currency' => 'GB',
                        ],
                        [
                            'amount'   => '10.00',
                            'currency' => 'EUR',
                        ],
                    ],
                ],
                [
                    'locale' => 'en_US',
                    'scope'  => 'mobile',
                    'data'   => [
                        [
                            'amount'   => '25.30',
                            'currency' => 'GB',
                        ],
                        [
                            'amount'   => '12.30',
                            'currency' => 'EUR',
                        ],
                    ],
                ],
                [
                    'locale' => 'en_US',
                    'scope'  => 'ecommerce',
                    'data'   => [
                        [
                            'amount'   => '25.30',
                            'currency' => 'GB',
                        ],
                        [
                            'amount'   => '12.30',
                            'currency' => 'EUR',
                        ],
                    ],
                ],
            ],
        ];

        $this->normalize($standardPriceProductValue, 'flat', [])->shouldReturn(
            [
                'a_price-GB-ecommerce-fr_FR'  => '25.30',
                'a_price-EUR-ecommerce-fr_FR' => '10.00',
                'a_price-GB-mobile-en_US'     => '25.30',
                'a_price-EUR-mobile-en_US'    => '12.30',
                'a_price-GB-ecommerce-en_US'  => '25.30',
                'a_price-EUR-ecommerce-en_US' => '12.30',
            ]
        );
    }
}
