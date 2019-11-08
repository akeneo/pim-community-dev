<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\ValuesFiller;

use Akeneo\Channel\Component\Model\Channel;
use Akeneo\Channel\Component\Model\Currency;
use Akeneo\Channel\Component\Model\Locale;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\FillMissingProductValues;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Test\Common\Structure\Attribute\Builder;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;

class FillMissingProductValuesSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $familyRepository,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        GetAttributes $getAttributes
    ) {
        $family = new Family();

        $family->addAttribute(
            (new Builder())->aTextAttribute()->withCode('name')->build()
        );
        $family->addAttribute(
            (new Builder())->aTextAttribute()->withCode('localizable_name')->localizable()->build()
        );
        $family->addAttribute(
            (new Builder())->aTextAttribute()->withCode('scopable_name')->scopable()->build()
        );
        $family->addAttribute(
            (new Builder())->aTextAttribute()->withCode('localizable_scopable_name')->localizable()->scopable()->build()
        );
        $family->addAttribute(
            (new Builder())->aTextAttribute()->withCode('123')->build()
        );

        $familyWithPrice = new Family();

        $familyWithPrice->addAttribute(
            (new Builder())->aPriceCollectionAttribute()->withCode('price')->build()
        );
        $familyWithPrice->addAttribute(
            (new Builder())->aPriceCollectionAttribute()->withCode('localizable_price')->localizable()->build()
        );
        $familyWithPrice->addAttribute(
            (new Builder())->aPriceCollectionAttribute()->withCode('scopable_price')->scopable()->build()
        );
        $familyWithPrice->addAttribute(
            (new Builder())->aPriceCollectionAttribute()->withCode('localizable_scopable_price')->localizable()->scopable()->build()
        );

        $familyRepository->findOneByIdentifier('shoes')->willReturn($family);
        $familyRepository->findOneByIdentifier('family_with_price')->willReturn($familyWithPrice);

        $deDe = new Locale();
        $enUs = new Locale();
        $frFR = new Locale();
        $deDe->setCode('de_DE');
        $enUs->setCode('en_US');
        $frFR->setCode('fr_FR');

        $USD = new Currency();
        $EUR = new Currency();
        $AED = new Currency();
        $USD->setCode('USD');
        $EUR->setCode('EUR');
        $AED->setCode('AED');

        $tablet = new Channel();
        $tablet->setCode('tablet');
        $tablet->setLocales([$enUs, $frFR]);
        $tablet->setCurrencies([$AED, $EUR]);

        $ecommerce = new Channel();
        $ecommerce->setCode('ecommerce');
        $ecommerce->setLocales([$frFR, $deDe]);
        $ecommerce->setCurrencies([$USD, $EUR]);

        $channelRepository->findAll()->willReturn([$tablet, $ecommerce]);
        $localeRepository->getActivatedLocales()->willReturn([$enUs, $frFR, $deDe]);

        $this->beConstructedWith($familyRepository, $channelRepository, $localeRepository, $getAttributes);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FillMissingProductValues::class);
    }

    function it_creates_all_missing_values(GetAttributes $getAttributes)
    {
        $getAttributes->forCodes([])->willReturn([]);

        $this->fromStandardFormat([
            'family' => 'shoes',
            'values' => []
        ])->shouldBeLike(
            [
                'family' => 'shoes',
                'values' => [
                    'name' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => null
                        ]
                    ],
                    'localizable_name' => [
                        [
                            'scope' => null,
                            'locale' => 'en_US',
                            'data' => null
                        ],
                        [
                            'scope' => null,
                            'locale' => 'fr_FR',
                            'data' => null
                        ],
                        [
                            'scope' => null,
                            'locale' => 'de_DE',
                            'data' => null
                        ],

                    ],
                    'scopable_name' => [
                        [
                            'scope' => 'tablet',
                            'locale' => null,
                            'data' => null
                        ],
                        [
                            'scope' => 'ecommerce',
                            'locale' => null,
                            'data' => null
                        ],
                    ],
                    'localizable_scopable_name' => [
                        [
                            'scope' => 'tablet',
                            'locale' => 'en_US',
                            'data' => null
                        ],
                        [
                            'scope' => 'tablet',
                            'locale' => 'fr_FR',
                            'data' => null
                        ],
                        [
                            'scope' => 'ecommerce',
                            'locale' => 'fr_FR',
                            'data' => null
                        ],
                        [
                            'scope' => 'ecommerce',
                            'locale' => 'de_DE',
                            'data' => null
                        ],
                    ],
                    '123' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => null
                        ]
                    ],
                ]
            ]
        );
    }

    function it_correctly_merges_the_null_values_without_replacing_existing_values(GetAttributes $getAttributes)
    {
        $getAttributes->forCodes(['name', 123, 'localizable_name', 'scopable_name', 'localizable_scopable_name'])->willReturn([
            'name' => new Attribute('name', AttributeTypes::TEXT, [], false, false, null, false, 'text', []),
            '123' => new Attribute('123', AttributeTypes::TEXT, [], false, false, null, false, 'text', []),
            'localizable_name' => new Attribute('localizable_name', AttributeTypes::TEXT, [], true, false, null, false, 'text', []),
            'scopable_name' => new Attribute('scopable_name', AttributeTypes::TEXT, [], false, true, null, false, 'text', []),
            'localizable_scopable_name' => new Attribute('localizable_scopable_name', AttributeTypes::TEXT, [], true, true, null, false, 'text', []),
        ]);

        $this->fromStandardFormat([
            'family' => 'shoes',
            'values' => [
                'name' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => 'value_name'
                    ]
                ],
                '123' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => 'value_123',
                    ]
                ],
                'localizable_name' => [
                    [
                        'scope' => null,
                        'locale' => 'fr_FR',
                        'data' => 'value_localizable_name_fr_FR'
                    ]
                ],
                'scopable_name' => [
                    [
                        'scope' => 'ecommerce',
                        'locale' => null,
                        'data' => 'value_scopable_name_ecommerce'
                    ]
                ],
                'localizable_scopable_name' => [
                    [
                        'scope' => 'ecommerce',
                        'locale' => 'de_DE',
                        'data' => 'value_localizable_scopable_name_de_DE'
                    ],
                    [
                        'scope' => 'tablet',
                        'locale' => 'fr_FR',
                        'data' => 'value_localizable_scopable_name_fr_FR'
                    ]
                ],
            ]
        ])->shouldBeLike(
            [
                'family' => 'shoes',
                'values' => [
                    'name' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'value_name'
                        ]
                    ],
                    'localizable_name' => [
                        [
                            'scope' => null,
                            'locale' => 'en_US',
                            'data' => null
                        ],
                        [
                            'scope' => null,
                            'locale' => 'fr_FR',
                            'data' => 'value_localizable_name_fr_FR'
                        ],
                        [
                            'scope' => null,
                            'locale' => 'de_DE',
                            'data' => null
                        ],
                    ],
                    'scopable_name' => [
                        [
                            'scope' => 'tablet',
                            'locale' => null,
                            'data' => null
                        ],
                        [
                            'scope' => 'ecommerce',
                            'locale' => null,
                            'data' => 'value_scopable_name_ecommerce'
                        ],
                    ],
                    'localizable_scopable_name' => [
                        [
                            'scope' => 'tablet',
                            'locale' => 'en_US',
                            'data' => null
                        ],
                        [
                            'scope' => 'tablet',
                            'locale' => 'fr_FR',
                            'data' => 'value_localizable_scopable_name_fr_FR'
                        ],
                        [
                            'scope' => 'ecommerce',
                            'locale' => 'fr_FR',
                            'data' => null
                        ],
                        [
                            'scope' => 'ecommerce',
                            'locale' => 'de_DE',
                            'data' => 'value_localizable_scopable_name_de_DE'
                        ],
                    ],
                    '123' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => 'value_123',
                        ]
                    ],
                ],
            ]
        );
    }

    function it_does_nothing_on_products_without_any_family()
    {
        $this->fromStandardFormat([
            'family' => null,
            'values' => [
                'localizable_name' => [
                    [
                        'scope' => null,
                        'locale' => 'fr_FR',
                        'data' => 'foo'
                    ]
                ],
            ]
        ])->shouldBeLike(
            [
                'family' => null,
                'values' => [
                    'localizable_name' => [
                        [
                            'scope' => null,
                            'locale' => 'fr_FR',
                            'data' => 'foo'
                        ]
                    ],
                ]
            ]
        );
    }

    function it_creates_all_null_price_values(GetAttributes $getAttributes)
    {
        $getAttributes->forCodes([])->willReturn([]);

        $this->fromStandardFormat(
            [
                'family' => 'family_with_price',
                'values' => []
            ]
        )->shouldBeLike(
            [
                'family' => 'family_with_price',
                'values' => [
                    'price' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => [
                                ['currency' => 'AED', 'amount' => null],
                                ['currency' => 'EUR', 'amount' => null],
                                ['currency' => 'USD', 'amount' => null],
                            ],
                        ],
                    ],
                    'localizable_price' => [
                        [
                            'scope' => null,
                            'locale' => 'en_US',
                            'data' => [
                                ['currency' => 'AED', 'amount' => null],
                                ['currency' => 'EUR', 'amount' => null],
                                ['currency' => 'USD', 'amount' => null],
                            ],
                        ],
                        [
                            'scope' => null,
                            'locale' => 'fr_FR',
                            'data' => [
                                ['currency' => 'AED', 'amount' => null],
                                ['currency' => 'EUR', 'amount' => null],
                                ['currency' => 'USD', 'amount' => null],
                            ],
                        ],
                        [
                            'scope' => null,
                            'locale' => 'de_DE',
                            'data' => [
                                ['currency' => 'AED', 'amount' => null],
                                ['currency' => 'EUR', 'amount' => null],
                                ['currency' => 'USD', 'amount' => null],
                            ],
                        ],
                    ],
                    'scopable_price' => [
                        [
                            'scope' => 'tablet',
                            'locale' => null,
                            'data' => [
                                ['currency' => 'AED', 'amount' => null],
                                ['currency' => 'EUR', 'amount' => null],
                            ],
                        ],
                        [
                            'scope' => 'ecommerce',
                            'locale' => null,
                            'data' => [
                                ['currency' => 'EUR', 'amount' => null],
                                ['currency' => 'USD', 'amount' => null],
                            ],
                        ],
                    ],
                    'localizable_scopable_price' => [
                        [
                            'scope' => 'tablet',
                            'locale' => 'en_US',
                            'data' => [
                                ['currency' => 'AED', 'amount' => null],
                                ['currency' => 'EUR', 'amount' => null],
                            ]
                        ],
                        [
                            'scope' => 'tablet',
                            'locale' => 'fr_FR',
                            'data' => [
                                ['currency' => 'AED', 'amount' => null],
                                ['currency' => 'EUR', 'amount' => null],
                            ]
                        ],
                        [
                            'scope' => 'ecommerce',
                            'locale' => 'fr_FR',
                            'data' => [
                                ['currency' => 'EUR', 'amount' => null],
                                ['currency' => 'USD', 'amount' => null],
                            ]
                        ],
                        [
                            'scope' => 'ecommerce',
                            'locale' => 'de_DE',
                            'data' => [
                                ['currency' => 'EUR', 'amount' => null],
                                ['currency' => 'USD', 'amount' => null],
                            ]
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * The order of the price collection in the values matters: the prices MUST be sorted by currency code
     */
    function it_does_not_replace_existing_price_values(GetAttributes $getAttributes)
    {
        $getAttributes->forCodes(['price', 'scopable_price', 'localizable_price', 'localizable_scopable_price'])->willReturn([
            'price' => new Attribute('price', AttributeTypes::PRICE_COLLECTION, [], false, false, null, false, 'text', []),
            'scopable_price' => new Attribute('scopable_price', AttributeTypes::PRICE_COLLECTION, [], false, true, null, false, 'text', []),
            'localizable_price' => new Attribute('localizable_price', AttributeTypes::PRICE_COLLECTION, [], true, false, null, false, 'text', []),
            'localizable_scopable_price' => new Attribute('localizable_scopable_price', AttributeTypes::PRICE_COLLECTION, [], true, true, null, false, 'text', []),
        ]);

        $this->fromStandardFormat(
            [
                'family' => 'family_with_price',
                'values' => [
                    'price' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => [
                                ['currency' => 'EUR', 'amount' => '10.00'],
                            ],
                        ],
                    ],
                    'scopable_price' => [
                        [
                            'scope' => 'tablet',
                            'locale' => null,
                            'data' => [
                                ['currency' => 'AED', 'amount' => '10.00'],
                            ],
                        ],
                    ],
                    'localizable_price' => [
                        [
                            'scope' => null,
                            'locale' => 'en_US',
                            'data' => [
                                ['currency' => 'USD', 'amount' => '10.00'],
                            ],
                        ],
                        [
                            'scope' => null,
                            'locale' => 'de_DE',
                            'data' => [
                                ['currency' => 'USD', 'amount' => '10.00'],
                                ['currency' => 'AED', 'amount' => '10.00'],
                            ],
                        ],
                    ],
                    'localizable_scopable_price' => [
                        [
                            'scope' => 'tablet',
                            'locale' => 'en_US',
                            'data' => [
                                ['currency' => 'AED', 'amount' => '10.00'],
                            ]
                        ],
                    ],
                ]
            ]
        )->shouldBeLike(
            [
                'family' => 'family_with_price',
                'values' => [
                    'price' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => [
                                ['currency' => 'AED', 'amount' => null],
                                ['currency' => 'EUR', 'amount' => '10.00'],
                                ['currency' => 'USD', 'amount' => null],
                            ],
                        ],
                    ],
                    'localizable_price' => [
                        [
                            'scope' => null,
                            'locale' => 'en_US',
                            'data' => [
                                ['currency' => 'AED', 'amount' => null],
                                ['currency' => 'EUR', 'amount' => null],
                                ['currency' => 'USD', 'amount' => '10.00'],
                            ],
                        ],
                        [
                            'scope' => null,
                            'locale' => 'fr_FR',
                            'data' => [
                                ['currency' => 'AED', 'amount' => null],
                                ['currency' => 'EUR', 'amount' => null],
                                ['currency' => 'USD', 'amount' => null],
                            ],
                        ],
                        [
                            'scope' => null,
                            'locale' => 'de_DE',
                            'data' => [
                                ['currency' => 'AED', 'amount' => '10.00'],
                                ['currency' => 'EUR', 'amount' => null],
                                ['currency' => 'USD', 'amount' => '10.00'],
                            ],
                        ],
                    ],
                    'scopable_price' => [
                        [
                            'scope' => 'tablet',
                            'locale' => null,
                            'data' => [
                                ['currency' => 'AED', 'amount' => '10.00'],
                                ['currency' => 'EUR', 'amount' => null],
                            ],
                        ],
                        [
                            'scope' => 'ecommerce',
                            'locale' => null,
                            'data' => [
                                ['currency' => 'EUR', 'amount' => null],
                                ['currency' => 'USD', 'amount' => null],
                            ],
                        ],
                    ],
                    'localizable_scopable_price' => [
                        [
                            'scope' => 'tablet',
                            'locale' => 'en_US',
                            'data' => [
                                ['currency' => 'AED', 'amount' => '10.00'],
                                ['currency' => 'EUR', 'amount' => null],
                            ]
                        ],
                        [
                            'scope' => 'tablet',
                            'locale' => 'fr_FR',
                            'data' => [
                                ['currency' => 'AED', 'amount' => null],
                                ['currency' => 'EUR', 'amount' => null],
                            ]
                        ],
                        [
                            'scope' => 'ecommerce',
                            'locale' => 'fr_FR',
                            'data' => [
                                ['currency' => 'EUR', 'amount' => null],
                                ['currency' => 'USD', 'amount' => null],
                            ]
                        ],
                        [
                            'scope' => 'ecommerce',
                            'locale' => 'de_DE',
                            'data' => [
                                ['currency' => 'EUR', 'amount' => null],
                                ['currency' => 'USD', 'amount' => null],
                            ]
                        ],
                    ],
                ],
            ]
        );
    }
}
