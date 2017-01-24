<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\Product;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\DateSanitizer;
use Akeneo\Test\Integration\MediaSanitizer;
use Akeneo\Test\Integration\TestCase;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\Response;

class SuccessListProductIntegration extends TestCase
{
    /** @var Collection */
    private $products;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        if (1 === self::$count) {
            // no locale, no scope, 1 category
            $this->createProduct('simple', [
                'categories' => ['master'],
                'values'     => [
                    'a_metric' => [
                        ['data' => ['amount' => 10, 'unit' => 'KILOWATT'], 'locale' => null, 'scope' => null]
                    ],
                    'a_text' => [
                        ['data' => 'Text', 'locale' => null, 'scope' => null]
                    ]
                ]
            ]);

            // localizable, categorized in 1 tree (master)
            $this->createProduct('localizable', [
                'categories' => ['categoryB'],
                'values'     => [
                    'a_localizable_image' => [
                        ['data' => $this->getFixturePath('akeneo.jpg'), 'locale' => 'en_US', 'scope' => null],
                        ['data' => $this->getFixturePath('akeneo.jpg'), 'locale' => 'fr_FR', 'scope' => null],
                        ['data' => $this->getFixturePath('akeneo.jpg'), 'locale' => 'zh_CN', 'scope' => null]
                    ]
                ]
            ]);

            // scopable, categorized in 1 tree (master)
            $this->createProduct('scopable', [
                'categories' => ['categoryA1', 'categoryA2'],
                'values'     => [
                    'a_scopable_price' => [
                        [
                            'locale' => null,
                            'scope'  => 'ecommerce',
                            'data'   => [
                                ['amount' => '10.50', 'currency' => 'EUR'],
                                ['amount' => '11.50', 'currency' => 'USD'],
                                ['amount' => '78.77', 'currency' => 'CNY']
                            ]
                        ],
                        [
                            'locale' => null,
                            'scope'  => 'tablet',
                            'data'   => [
                                ['amount' => '10.50', 'currency' => 'EUR'],
                                ['amount' => '11.50', 'currency' => 'USD'],
                                ['amount' => '78.77', 'currency' => 'CNY']
                            ]
                        ]
                    ]
                ]
            ]);

            // localizable & scopable, categorized in 2 trees (master and master_china)
            $this->createProduct('localizable_and_scopable', [
                'categories' => ['categoryA', 'master_china'],
                'values'     => [
                    'a_localized_and_scopable_text_area' => [
                        ['data' => 'Big description', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                        ['data' => 'Medium description', 'locale' => 'en_US', 'scope' => 'tablet'],
                        ['data' => 'Grande description', 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
                        ['data' => 'Description moyenne', 'locale' => 'fr_FR', 'scope' => 'tablet'],
                        ['data' => 'hum...', 'locale' => 'zh_CN', 'scope' => 'ecommerce_china'],
                    ]
                ]
            ]);

            $this->createProduct('product_china', [
                'categories' => ['master_china']
            ]);

            $this->createProduct('product_without_category', [
                'values' => [
                    'a_yes_no' => [
                        ['data' => true, 'locale' => null, 'scope' => null]
                    ]
                ]
            ]);
        }

        $this->products = $this->get('pim_catalog.repository.product')->findAll();
    }

    /**
     * Get all products, whatever locale, channel, category
     */
    public function testListProductsWithoutParameter()
    {
        $client = static::createClient();

        $client->request('GET', 'api/rest/v1/products');
        $expected = [
            [
                'identifier'    => 'simple',
                'family'        => null,
                'groups'        => [],
                'variant_group' => null,
                'categories'    => ['master'],
                'enabled'       => true,
                'values'        => [
                    'a_metric' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => [
                                'amount' => '10.0000',
                                'unit'   => 'KILOWATT'
                            ]
                        ]
                    ],
                    'a_text' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => 'Text'
                        ]
                    ]
                ],
                'created'       => '2017-01-23T11:44:25+01:00',
                'updated'       => '2017-01-23T11:44:25+01:00',
                'associations'  => [],
            ],
            [
                'identifier'    => 'localizable',
                'family'        => null,
                'groups'        => [],
                'variant_group' => null,
                'categories'    => ['categoryB'],
                'enabled'       => true,
                'values'        => [
                    'a_localizable_image' => [
                        ['locale' => 'en_US', 'scope' => null, 'data' => '8/5/6/e/856e7f47e3e53415d9c4ce8efe9bb51c8b2c68d5_akeneo.jpg'],
                        ['locale' => 'fr_FR', 'scope' => null, 'data' => '5/5/9/6/559681bb0b2df7ae0eaf3bda76af5819c08bd6ae_akeneo.jpg'],
                        ['locale' => 'zh_CN', 'scope' => null, 'data' => '5/5/9/6/559681bb0b2df7ae0eaf3bda76af5819c08bd6ae_akeneo.jpg']
                    ]
                ],
                'created'       => '2017-01-23T11:44:25+01:00',
                'updated'       => '2017-01-23T11:44:25+01:00',
                'associations'  => [],
            ],
            [
                'identifier'    => 'scopable',
                'family'        => null,
                'groups'        => [],
                'variant_group' => null,
                'categories'    => ['categoryA1', 'categoryA2'],
                'enabled'       => true,
                'values'        => [
                    'a_scopable_price' => [
                        [
                            'locale' => null,
                            'scope'  => 'ecommerce',
                            'data'   => [
                                ['amount' => '10.50', 'currency' => 'EUR'],
                                ['amount' => '11.50', 'currency' => 'USD'],
                                ['amount' => '78.77', 'currency' => 'CNY']
                            ]
                        ],
                        [
                            'locale' => null,
                            'scope'  => 'tablet',
                            'data'   => [
                                ['amount' => '10.50', 'currency' => 'EUR'],
                                ['amount' => '11.50', 'currency' => 'USD'],
                                ['amount' => '78.77', 'currency' => 'CNY']
                        ]
                        ]
                    ]
                ],
                'created'       => '2017-01-23T11:44:25+01:00',
                'updated'       => '2017-01-23T11:44:25+01:00',
                'associations'  => [],
            ],
            [
                'identifier'    => 'localizable_and_scopable',
                'family'        => null,
                'groups'        => [],
                'variant_group' => null,
                'categories'    => ['categoryA', 'master_china'],
                'enabled'       => true,
                'values'        => [
                    'a_localized_and_scopable_text_area' => [
                        ['locale' => 'en_US', 'scope' => 'ecommerce', 'data' => 'Big description'],
                        ['locale' => 'en_US', 'scope' => 'tablet', 'data' => 'Medium description'],
                        ['locale' => 'fr_FR', 'scope' => 'ecommerce', 'data' => 'Grande description'],
                        ['locale' => 'fr_FR', 'scope' => 'tablet', 'data' => 'Description moyenne'],
                        ['locale' => 'zh_CN', 'scope' => 'ecommerce_china', 'data' => 'hum...'],
                    ]
                ],
                'created'       => '2017-01-23T11:44:25+01:00',
                'updated'       => '2017-01-23T11:44:25+01:00',
                'associations'  => [],
            ],
            [
                'identifier'    => 'product_china',
                'family'        => null,
                'groups'        => [],
                'variant_group' => null,
                'categories'    => ['master_china'],
                'enabled'       => true,
                'values'        => [],
                'created'       => '2017-01-23T11:44:25+01:00',
                'updated'       => '2017-01-23T11:44:25+01:00',
                'associations'  => [],
            ],
            [
                'identifier'    => 'product_without_category',
                'family'        => null,
                'groups'        => [],
                'variant_group' => null,
                'categories'    => [],
                'enabled'       => true,
                'values'        => [
                    'a_yes_no' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => true
                        ]
                    ]
                ],
                'created'       => '2017-01-23T11:44:25+01:00',
                'updated'       => '2017-01-23T11:44:25+01:00',
                'associations'  => [],
            ]
        ];

        $this->assertResponse($client->getResponse(), $expected);
    }

    /**
     * Channel "ecommerce" has only "en_US" activated locale and it category tree linked is "master"
     * So PV are returned only if:
     *    - scope = "ecommerce"
     *    - locale = "en_US" or null
     * Then only products in "master" tree are returned
     */
    public function testListProductsWithEcommerceChannel()
    {
        $client = static::createClient();

        $client->request('GET', 'api/rest/v1/products?channel=ecommerce');
        $expected = [
            [
                'identifier'    => 'simple',
                'family'        => null,
                'groups'        => [],
                'variant_group' => null,
                'categories'    => ['master'],
                'enabled'       => true,
                'values'        => [
                    'a_metric' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => [
                                'amount' => '10.0000',
                                'unit'   => 'KILOWATT'
                            ]
                        ]
                    ],
                    'a_text' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => 'Text'
                        ]
                    ]
                ],
                'created'       => '2017-01-23T11:44:25+01:00',
                'updated'       => '2017-01-23T11:44:25+01:00',
                'associations'  => [],
            ],
            [
                'identifier'    => 'localizable',
                'family'        => null,
                'groups'        => [],
                'variant_group' => null,
                'categories'    => ['categoryB'],
                'enabled'       => true,
                'values'        => [
                    'a_localizable_image' => [
                        ['locale' => 'en_US', 'scope' => null, 'data' => '8/5/6/e/856e7f47e3e53415d9c4ce8efe9bb51c8b2c68d5_akeneo.jpg'],
                    ]
                ],
                'created'       => '2017-01-23T11:44:25+01:00',
                'updated'       => '2017-01-23T11:44:25+01:00',
                'associations'  => [],
            ],
            [
                'identifier'    => 'scopable',
                'family'        => null,
                'groups'        => [],
                'variant_group' => null,
                'categories'    => ['categoryA1', 'categoryA2'],
                'enabled'       => true,
                'values'        => [
                    'a_scopable_price' => [
                        [
                            'locale' => null,
                            'scope'  => 'ecommerce',
                            'data'   => [
                                ['amount' => '10.50', 'currency' => 'EUR'],
                                ['amount' => '11.50', 'currency' => 'USD'],
                                ['amount' => '78.77', 'currency' => 'CNY']
                            ]
                        ]
                    ]
                ],
                'created'       => '2017-01-23T11:44:25+01:00',
                'updated'       => '2017-01-23T11:44:25+01:00',
                'associations'  => [],
            ],
            [
                'identifier'    => 'localizable_and_scopable',
                'family'        => null,
                'groups'        => [],
                'variant_group' => null,
                'categories'    => ['categoryA', 'master_china'],
                'enabled'       => true,
                'values'        => [
                    'a_localized_and_scopable_text_area' => [
                        ['locale' => 'en_US', 'scope' => 'ecommerce', 'data' => 'Big description'],
                    ]
                ],
                'created'       => '2017-01-23T11:44:25+01:00',
                'updated'       => '2017-01-23T11:44:25+01:00',
                'associations'  => [],
            ]
        ];

        $this->assertResponse($client->getResponse(), $expected);
    }

    /**
     * Channel "tablet" has "fr_FR" and "en_US" activated locales and it category tree linked is "master"
     * So PV are returned only if:
     *     - scope = "tablet"
     *     - locale = "en_US", "fr_FR" or null
     * Then only products in "master" tree are returned
     */
    public function testListProductsWithTabletChannel()
    {
        $client = static::createClient();

        $client->request('GET', 'api/rest/v1/products?channel=tablet');
        $expected = [
            [
                'identifier'    => 'simple',
                'family'        => null,
                'groups'        => [],
                'variant_group' => null,
                'categories'    => ['master'],
                'enabled'       => true,
                'values'        => [
                    'a_metric' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => [
                                'amount' => '10.0000',
                                'unit'   => 'KILOWATT'
                            ]
                        ]
                    ],
                    'a_text' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => 'Text'
                        ]
                    ]
                ],
                'created'       => '2017-01-23T11:44:25+01:00',
                'updated'       => '2017-01-23T11:44:25+01:00',
                'associations'  => [],
            ],
            [
                'identifier'    => 'localizable',
                'family'        => null,
                'groups'        => [],
                'variant_group' => null,
                'categories'    => ['categoryB'],
                'enabled'       => true,
                'values'        => [
                    'a_localizable_image' => [
                        ['locale' => 'en_US', 'scope' => null, 'data' => '8/5/6/e/856e7f47e3e53415d9c4ce8efe9bb51c8b2c68d5_akeneo.jpg'],
                        ['locale' => 'fr_FR', 'scope' => null, 'data' => '5/5/9/6/559681bb0b2df7ae0eaf3bda76af5819c08bd6ae_akeneo.jpg'],
                    ]
                ],
                'created'       => '2017-01-23T11:44:25+01:00',
                'updated'       => '2017-01-23T11:44:25+01:00',
                'associations'  => [],
            ],
            [
                'identifier'    => 'scopable',
                'family'        => null,
                'groups'        => [],
                'variant_group' => null,
                'categories'    => ['categoryA1', 'categoryA2'],
                'enabled'       => true,
                'values'        => [
                    'a_scopable_price' => [
                        [
                            'locale' => null,
                            'scope'  => 'tablet',
                            'data'   => [
                                ['amount' => '10.50', 'currency' => 'EUR'],
                                ['amount' => '11.50', 'currency' => 'USD'],
                                ['amount' => '78.77', 'currency' => 'CNY']
                            ]
                        ]
                    ]
                ],
                'created'       => '2017-01-23T11:44:25+01:00',
                'updated'       => '2017-01-23T11:44:25+01:00',
                'associations'  => [],
            ],
            [
                'identifier'    => 'localizable_and_scopable',
                'family'        => null,
                'groups'        => [],
                'variant_group' => null,
                'categories'    => ['categoryA', 'master_china'],
                'enabled'       => true,
                'values'        => [
                    'a_localized_and_scopable_text_area' => [
                        ['locale' => 'en_US', 'scope' => 'tablet', 'data' => 'Medium description'],
                        ['locale' => 'fr_FR', 'scope' => 'tablet', 'data' => 'Description moyenne'],
                    ]
                ],
                'created'       => '2017-01-23T11:44:25+01:00',
                'updated'       => '2017-01-23T11:44:25+01:00',
                'associations'  => [],
            ]
        ];

        $this->assertResponse($client->getResponse(), $expected);
    }

    /**
     * Filter on channel "tablet" and locale "fr_FR"
     * So PV are returned only if:
     *     - scope = "tablet"
     *     - locale = "fr_FR" or null
     * Then only products in "master" tree are returned
     */
    public function testListProductsWithTabletChannelAndFRLocale()
    {
        $client = static::createClient();

        $client->request('GET', 'api/rest/v1/products?channel=tablet&locales=fr_FR');
        $expected = [
            [
                'identifier'    => 'simple',
                'family'        => null,
                'groups'        => [],
                'variant_group' => null,
                'categories'    => ['master'],
                'enabled'       => true,
                'values'        => [
                    'a_metric' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => [
                                'amount' => '10.0000',
                                'unit'   => 'KILOWATT'
                            ]
                        ]
                    ],
                    'a_text' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => 'Text'
                        ]
                    ]
                ],
                'created'       => '2017-01-23T11:44:25+01:00',
                'updated'       => '2017-01-23T11:44:25+01:00',
                'associations'  => [],
            ],
            [
                'identifier'    => 'localizable',
                'family'        => null,
                'groups'        => [],
                'variant_group' => null,
                'categories'    => ['categoryB'],
                'enabled'       => true,
                'values'        => [
                    'a_localizable_image' => [
                        ['locale' => 'fr_FR', 'scope' => null, 'data' => '5/5/9/6/559681bb0b2df7ae0eaf3bda76af5819c08bd6ae_akeneo.jpg'],
                    ]
                ],
                'created'       => '2017-01-23T11:44:25+01:00',
                'updated'       => '2017-01-23T11:44:25+01:00',
                'associations'  => [],
            ],
            [
                'identifier'    => 'scopable',
                'family'        => null,
                'groups'        => [],
                'variant_group' => null,
                'categories'    => ['categoryA1', 'categoryA2'],
                'enabled'       => true,
                'values'        => [
                    'a_scopable_price' => [
                        [
                            'locale' => null,
                            'scope'  => 'tablet',
                            'data'   => [
                                ['amount' => '10.50', 'currency' => 'EUR'],
                                ['amount' => '11.50', 'currency' => 'USD'],
                                ['amount' => '78.77', 'currency' => 'CNY']
                            ]
                        ]
                    ]
                ],
                'created'       => '2017-01-23T11:44:25+01:00',
                'updated'       => '2017-01-23T11:44:25+01:00',
                'associations'  => [],
            ],
            [
                'identifier'    => 'localizable_and_scopable',
                'family'        => null,
                'groups'        => [],
                'variant_group' => null,
                'categories'    => ['categoryA', 'master_china'],
                'enabled'       => true,
                'values'        => [
                    'a_localized_and_scopable_text_area' => [
                        ['locale' => 'fr_FR', 'scope' => 'tablet', 'data' => 'Description moyenne'],
                    ]
                ],
                'created'       => '2017-01-23T11:44:25+01:00',
                'updated'       => '2017-01-23T11:44:25+01:00',
                'associations'  => [],
            ]
        ];

        $this->assertResponse($client->getResponse(), $expected);
    }

    /**
     * Channel "ecommerce_china" has "CNY" activated locale and it category tree linked is "master_china"
     * So PV are returned only if:
     *     - scope = "ecommerce_china"
     *     - locale = "en_US", "zh_CN" or null
     * Then only products in "master_china" tree are returned
     */
    public function testListProductsWithEcommerceChinaChannel()
    {
        $client = static::createClient();

        $client->request('GET', 'api/rest/v1/products?channel=ecommerce_china');
        $expected = [
            [
                'identifier'    => 'localizable_and_scopable',
                'family'        => null,
                'groups'        => [],
                'variant_group' => null,
                'categories'    => ['categoryA', 'master_china'],
                'enabled'       => true,
                'values'        => [
                    'a_localized_and_scopable_text_area' => [
                        ['locale' => 'zh_CN', 'scope' => 'ecommerce_china', 'data' => 'hum...'],
                    ]
                ],
                'created'       => '2017-01-23T11:44:25+01:00',
                'updated'       => '2017-01-23T11:44:25+01:00',
                'associations'  => [],
            ],
            [
                'identifier'    => 'product_china',
                'family'        => null,
                'groups'        => [],
                'variant_group' => null,
                'categories'    => ['master_china'],
                'enabled'       => true,
                'values'        => [],
                'created'       => '2017-01-23T11:44:25+01:00',
                'updated'       => '2017-01-23T11:44:25+01:00',
                'associations'  => [],
            ]
        ];

        $this->assertResponse($client->getResponse(), $expected);
    }

    /**
     * Filter on locales "en_US" and "zh_CN"
     * So PV are returned only if:
     *     - locale = "en_US", "zh_CN" or null
     * Then we return all products (whatever the categories)
     */
    public function testListProductsWithENAndCNLocales()
    {
        $client = static::createClient();

        $client->request('GET', 'api/rest/v1/products?locales=en_US,zh_CN');
        $expected = [
            [
                'identifier'    => 'simple',
                'family'        => null,
                'groups'        => [],
                'variant_group' => null,
                'categories'    => ['master'],
                'enabled'       => true,
                'values'        => [
                    'a_metric' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => [
                                'amount' => '10.0000',
                                'unit'   => 'KILOWATT'
                            ]
                        ]
                    ],
                    'a_text' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => 'Text'
                        ]
                    ]
                ],
                'created'       => '2017-01-23T11:44:25+01:00',
                'updated'       => '2017-01-23T11:44:25+01:00',
                'associations'  => [],
            ],
            [
                'identifier'    => 'localizable',
                'family'        => null,
                'groups'        => [],
                'variant_group' => null,
                'categories'    => ['categoryB'],
                'enabled'       => true,
                'values'        => [
                    'a_localizable_image' => [
                        ['locale' => 'en_US', 'scope' => null, 'data' => '8/5/6/e/856e7f47e3e53415d9c4ce8efe9bb51c8b2c68d5_akeneo.jpg'],
                        ['locale' => 'zh_CN', 'scope' => null, 'data' => '5/5/9/6/559681bb0b2df7ae0eaf3bda76af5819c08bd6ae_akeneo.jpg']
                    ]
                ],
                'created'       => '2017-01-23T11:44:25+01:00',
                'updated'       => '2017-01-23T11:44:25+01:00',
                'associations'  => [],
            ],
            [
                'identifier'    => 'scopable',
                'family'        => null,
                'groups'        => [],
                'variant_group' => null,
                'categories'    => ['categoryA1', 'categoryA2'],
                'enabled'       => true,
                'values'        => [
                    'a_scopable_price' => [
                        [
                            'locale' => null,
                            'scope'  => 'ecommerce',
                            'data'   => [
                                ['amount' => '10.50', 'currency' => 'EUR'],
                                ['amount' => '11.50', 'currency' => 'USD'],
                                ['amount' => '78.77', 'currency' => 'CNY']
                            ]
                        ],
                        [
                            'locale' => null,
                            'scope'  => 'tablet',
                            'data'   => [
                                ['amount' => '10.50', 'currency' => 'EUR'],
                                ['amount' => '11.50', 'currency' => 'USD'],
                                ['amount' => '78.77', 'currency' => 'CNY']
                            ]
                        ]
                    ]
                ],
                'created'       => '2017-01-23T11:44:25+01:00',
                'updated'       => '2017-01-23T11:44:25+01:00',
                'associations'  => [],
            ],
            [
                'identifier'    => 'localizable_and_scopable',
                'family'        => null,
                'groups'        => [],
                'variant_group' => null,
                'categories'    => ['categoryA', 'master_china'],
                'enabled'       => true,
                'values'        => [
                    'a_localized_and_scopable_text_area' => [
                        ['locale' => 'en_US', 'scope' => 'ecommerce', 'data' => 'Big description'],
                        ['locale' => 'en_US', 'scope' => 'tablet', 'data' => 'Medium description'],
                        ['locale' => 'zh_CN', 'scope' => 'ecommerce_china', 'data' => 'hum...'],
                    ]
                ],
                'created'       => '2017-01-23T11:44:25+01:00',
                'updated'       => '2017-01-23T11:44:25+01:00',
                'associations'  => [],
            ],
            [
                'identifier'    => 'product_china',
                'family'        => null,
                'groups'        => [],
                'variant_group' => null,
                'categories'    => ['master_china'],
                'enabled'       => true,
                'values'        => [],
                'created'       => '2017-01-23T11:44:25+01:00',
                'updated'       => '2017-01-23T11:44:25+01:00',
                'associations'  => [],
            ],
            [
                'identifier'    => 'product_without_category',
                'family'        => null,
                'groups'        => [],
                'variant_group' => null,
                'categories'    => [],
                'enabled'       => true,
                'values'        => [
                    'a_yes_no' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => true,
                        ]
                    ]
                ],
                'created'       => '2017-01-23T11:44:25+01:00',
                'updated'       => '2017-01-23T11:44:25+01:00',
                'associations'  => [],
            ]
        ];

        $this->assertResponse($client->getResponse(), $expected);
    }

    /**
     * Filter on attributes
     */
    public function testListProductsWithFilteredAttributes()
    {
        $client = static::createClient();

        $client->request('GET', 'api/rest/v1/products?attributes=a_text');
        $expected = [
            [
                'identifier'    => 'simple',
                'family'        => null,
                'groups'        => [],
                'variant_group' => null,
                'categories'    => ['master'],
                'enabled'       => true,
                'values'        => [
                    'a_text' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => 'Text'
                        ]
                    ]
                ],
                'created'       => '2017-01-23T11:44:25+01:00',
                'updated'       => '2017-01-23T11:44:25+01:00',
                'associations'  => [],
            ],
            [
                'identifier'    => 'localizable',
                'family'        => null,
                'groups'        => [],
                'variant_group' => null,
                'categories'    => ['categoryB'],
                'enabled'       => true,
                'values'        => [],
                'created'       => '2017-01-23T11:44:25+01:00',
                'updated'       => '2017-01-23T11:44:25+01:00',
                'associations'  => [],
            ],
            [
                'identifier'    => 'scopable',
                'family'        => null,
                'groups'        => [],
                'variant_group' => null,
                'categories'    => ['categoryA1', 'categoryA2'],
                'enabled'       => true,
                'values'        => [],
                'created'       => '2017-01-23T11:44:25+01:00',
                'updated'       => '2017-01-23T11:44:25+01:00',
                'associations'  => [],
            ],
            [
                'identifier'    => 'localizable_and_scopable',
                'family'        => null,
                'groups'        => [],
                'variant_group' => null,
                'categories'    => ['categoryA', 'master_china'],
                'enabled'       => true,
                'values'        => [],
                'created'       => '2017-01-23T11:44:25+01:00',
                'updated'       => '2017-01-23T11:44:25+01:00',
                'associations'  => [],
            ],
            [
                'identifier'    => 'product_china',
                'family'        => null,
                'groups'        => [],
                'variant_group' => null,
                'categories'    => ['master_china'],
                'enabled'       => true,
                'values'        => [],
                'created'       => '2017-01-23T11:44:25+01:00',
                'updated'       => '2017-01-23T11:44:25+01:00',
                'associations'  => [],
            ],
            [
                'identifier'    => 'product_without_category',
                'family'        => null,
                'groups'        => [],
                'variant_group' => null,
                'categories'    => [],
                'enabled'       => true,
                'values'        => [],
                'created'       => '2017-01-23T11:44:25+01:00',
                'updated'       => '2017-01-23T11:44:25+01:00',
                'associations'  => [],
            ]
        ];

        $this->assertResponse($client->getResponse(), $expected);
    }

    /**
     * Filter with all params: one channel, two locales and attributes
     */
    public function testListProductsWithChannelLocalesAndAttributesParams()
    {
        $client = static::createClient();

        $client->request('GET', 'api/rest/v1/products?channel=tablet&locales=fr_FR&attributes=a_scopable_price,a_metric,a_localized_and_scopable_text_area');
        $expected = [
            [
                'identifier'    => 'simple',
                'family'        => null,
                'groups'        => [],
                'variant_group' => null,
                'categories'    => ['master'],
                'enabled'       => true,
                'values'        => [
                    'a_metric' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => [
                                'amount' => '10.0000',
                                'unit'   => 'KILOWATT'
                            ]
                        ]
                    ]
                ],
                'created'       => '2017-01-23T11:44:25+01:00',
                'updated'       => '2017-01-23T11:44:25+01:00',
                'associations'  => [],
            ],
            [
                'identifier'    => 'localizable',
                'family'        => null,
                'groups'        => [],
                'variant_group' => null,
                'categories'    => ['categoryB'],
                'enabled'       => true,
                'values'        => [],
                'created'       => '2017-01-23T11:44:25+01:00',
                'updated'       => '2017-01-23T11:44:25+01:00',
                'associations'  => [],
            ],
            [
                'identifier'    => 'scopable',
                'family'        => null,
                'groups'        => [],
                'variant_group' => null,
                'categories'    => ['categoryA1', 'categoryA2'],
                'enabled'       => true,
                'values'        => [
                    'a_scopable_price' => [
                        [
                            'locale' => null,
                            'scope'  => 'tablet',
                            'data'   => [
                                ['amount' => '10.50', 'currency' => 'EUR'],
                                ['amount' => '11.50', 'currency' => 'USD'],
                                ['amount' => '78.77', 'currency' => 'CNY']
                            ]
                        ]
                    ]
                ],
                'created'       => '2017-01-23T11:44:25+01:00',
                'updated'       => '2017-01-23T11:44:25+01:00',
                'associations'  => [],
            ],
            [
                'identifier'    => 'localizable_and_scopable',
                'family'        => null,
                'groups'        => [],
                'variant_group' => null,
                'categories'    => ['categoryA', 'master_china'],
                'enabled'       => true,
                'values'        => [
                    'a_localized_and_scopable_text_area' => [
                        ['locale' => 'fr_FR', 'scope' => 'tablet', 'data' => 'Description moyenne'],
                    ]
                ],
                'created'       => '2017-01-23T11:44:25+01:00',
                'updated'       => '2017-01-23T11:44:25+01:00',
                'associations'  => [],
            ]
        ];

        $this->assertResponse($client->getResponse(), $expected);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration(
            [Configuration::getTechnicalCatalogPath()],
            false
        );
    }

    /**
     * @param Response $response
     * @param array    $expected
     */
    private function assertResponse(Response $response, array $expected)
    {
        $result = json_decode($response->getContent(), true);

        foreach ($result as $index => $product) {
            $product = $this->sanitizeDateFields($product);
            $result[$index] = $this->sanitizeMediaAttributeData($product);

            if (isset($expected[$index])) {
                $expected[$index] = $this->sanitizeDateFields($expected[$index]);
                $expected[$index] = $this->sanitizeMediaAttributeData($expected[$index]);
            }
        }

        $this->assertSame($expected, $result);
    }

    /**
     * @param string $identifier
     * @param array  $data
     */
    private function createProduct($identifier, array $data = [])
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $this->get('pim_catalog.saver.product')->save($product);
    }

    /**
     * Replaces dates fields (created/updated) in the $data array by self::DATE_FIELD_COMPARISON.
     *
     * @param array $data
     *
     * @return array
     */
    private function sanitizeDateFields(array $data)
    {
        $data['created'] = DateSanitizer::sanitize($data['created']);
        $data['updated'] = DateSanitizer::sanitize($data['updated']);

        return $data;
    }

    /**
     * Replaces media attributes data in the $data array by self::MEDIA_ATTRIBUTE_DATA_COMPARISON.
     *
     * @param array $data
     *
     * @return array
     */
    private function sanitizeMediaAttributeData(array $data)
    {
        foreach ($data['values'] as $attributeCode => $values) {
            if (1 === preg_match('/.*(file|image).*/', $attributeCode)) {
                foreach ($values as $index => $value) {
                    $data['values'][$attributeCode][$index]['data'] = MediaSanitizer::sanitize($value['data']);
                }
            }
        }

        return $data;
    }
}
