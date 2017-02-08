<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\Product;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\Response;

class SuccessListProductIntegration extends AbstractProductTestCase
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
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products');
        $expected = [
            '_links'       => [
                'self'  => ['href' => 'http://localhost/api/rest/v1/products?page=1&limit=10'],
                'first' => ['href' => 'http://localhost/api/rest/v1/products?page=1&limit=10'],
                'last'  => ['href' => 'http://localhost/api/rest/v1/products?page=1&limit=10'],
            ],
            'current_page' => 1,
            'pages_count'  => 1,
            'items_count'  => 6,
            '_embedded'    => [
                'items' => [
                    [
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/products/simple']
                        ],
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
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/products/localizable']
                        ],
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
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/products/scopable']
                        ],
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
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/products/localizable_and_scopable']
                        ],
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
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/products/product_china']
                        ],
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
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/products/product_without_category']
                        ],
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
                ]
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
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products?channel=ecommerce');
        $expected = [
            '_links'       => [
                'self'  => ['href' => 'http://localhost/api/rest/v1/products?channel=ecommerce&page=1&limit=10'],
                'first' => ['href' => 'http://localhost/api/rest/v1/products?channel=ecommerce&page=1&limit=10'],
                'last'  => ['href' => 'http://localhost/api/rest/v1/products?channel=ecommerce&page=1&limit=10'],
            ],
            'current_page' => 1,
            'pages_count'  => 1,
            'items_count'  => 4,
            '_embedded'    => [
                'items' => [
                    [
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/products/simple']
                        ],
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
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/products/localizable']
                        ],
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
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/products/scopable']
                        ],
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
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/products/localizable_and_scopable']
                        ],
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
                ]
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
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products?channel=tablet');
        $expected = [
            '_links'       => [
                'self'  => ['href' => 'http://localhost/api/rest/v1/products?channel=tablet&page=1&limit=10'],
                'first' => ['href' => 'http://localhost/api/rest/v1/products?channel=tablet&page=1&limit=10'],
                'last'  => ['href' => 'http://localhost/api/rest/v1/products?channel=tablet&page=1&limit=10'],
            ],
            'current_page' => 1,
            'pages_count'  => 1,
            'items_count'  => 4,
            '_embedded'    => [
                'items' => [
                    [
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/products/simple']
                        ],
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
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/products/localizable']
                        ],
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
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/products/scopable']
                        ],
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
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/products/localizable_and_scopable']
                        ],
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
                ]
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
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products?channel=tablet&locales=fr_FR');
        $expected = [
            '_links'       => [
                'self'  => ['href' => 'http://localhost/api/rest/v1/products?channel=tablet&locales=fr_FR&page=1&limit=10'],
                'first' => ['href' => 'http://localhost/api/rest/v1/products?channel=tablet&locales=fr_FR&page=1&limit=10'],
                'last'  => ['href' => 'http://localhost/api/rest/v1/products?channel=tablet&locales=fr_FR&page=1&limit=10'],
            ],
            'current_page' => 1,
            'pages_count'  => 1,
            'items_count'  => 4,
            '_embedded'    => [
                'items' => [
                    [
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/products/simple']
                        ],
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
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/products/localizable']
                        ],
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
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/products/scopable']
                        ],
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
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/products/localizable_and_scopable']
                        ],
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
                ]
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
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products?channel=ecommerce_china');
        $expected = [
            '_links'       => [
                'self'  => ['href' => 'http://localhost/api/rest/v1/products?channel=ecommerce_china&page=1&limit=10'],
                'first' => ['href' => 'http://localhost/api/rest/v1/products?channel=ecommerce_china&page=1&limit=10'],
                'last'  => ['href' => 'http://localhost/api/rest/v1/products?channel=ecommerce_china&page=1&limit=10'],
            ],
            'current_page' => 1,
            'pages_count'  => 1,
            'items_count'  => 2,
            '_embedded'    => [
                'items' => [
                    [
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/products/localizable_and_scopable']
                        ],
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
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/products/product_china']
                        ],
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
                ]
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
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products?locales=en_US,zh_CN');
        $expected = [
            '_links'       => [
                'self'  => ['href' => 'http://localhost/api/rest/v1/products?locales=en_US%2Czh_CN&page=1&limit=10'],
                'first' => ['href' => 'http://localhost/api/rest/v1/products?locales=en_US%2Czh_CN&page=1&limit=10'],
                'last'  => ['href' => 'http://localhost/api/rest/v1/products?locales=en_US%2Czh_CN&page=1&limit=10'],
            ],
            'current_page' => 1,
            'pages_count'  => 1,
            'items_count'  => 6,
            '_embedded'    => [
                'items' => [
                    [
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/products/simple']
                        ],
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
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/products/localizable']
                        ],
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
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/products/scopable']
                        ],
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
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/products/localizable_and_scopable']
                        ],
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
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/products/product_china']
                        ],
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
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/products/product_without_category']
                        ],
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
                ]
            ]
        ];

        $this->assertResponse($client->getResponse(), $expected);
    }

    public function testListProductsWithFilteredAttributes()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products?attributes=a_text');
        $expected = [
            '_links'       => [
                'self'  => ['href' => 'http://localhost/api/rest/v1/products?attributes=a_text&page=1&limit=10'],
                'first' => ['href' => 'http://localhost/api/rest/v1/products?attributes=a_text&page=1&limit=10'],
                'last'  => ['href' => 'http://localhost/api/rest/v1/products?attributes=a_text&page=1&limit=10'],
            ],
            'current_page' => 1,
            'pages_count'  => 1,
            'items_count'  => 6,
            '_embedded'    => [
                'items' => [
                    [
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/products/simple']
                        ],
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
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/products/localizable']
                        ],
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
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/products/scopable']
                        ],
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
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/products/localizable_and_scopable']
                        ],
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
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/products/product_china']
                        ],
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
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/products/product_without_category']
                        ],
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
                ]
            ]
        ];

        $this->assertResponse($client->getResponse(), $expected);
    }

    public function testListProductsWithChannelLocalesAndAttributesParams()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products?channel=tablet&locales=fr_FR&attributes=a_scopable_price,a_metric,a_localized_and_scopable_text_area');
        $expected = [
            '_links'       => [
                'self'  => ['href' => 'http://localhost/api/rest/v1/products?channel=tablet&locales=fr_FR&attributes=a_scopable_price%2Ca_metric%2Ca_localized_and_scopable_text_area&page=1&limit=10'],
                'first' => ['href' => 'http://localhost/api/rest/v1/products?channel=tablet&locales=fr_FR&attributes=a_scopable_price%2Ca_metric%2Ca_localized_and_scopable_text_area&page=1&limit=10'],
                'last'  => ['href' => 'http://localhost/api/rest/v1/products?channel=tablet&locales=fr_FR&attributes=a_scopable_price%2Ca_metric%2Ca_localized_and_scopable_text_area&page=1&limit=10'],
            ],
            'current_page' => 1,
            'pages_count'  => 1,
            'items_count'  => 4,
            '_embedded'    => [
                'items' => [
                    [
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/products/simple']
                        ],
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
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/products/localizable']
                        ],
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
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/products/scopable']
                        ],
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
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/products/localizable_and_scopable']
                        ],
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
                ]
            ]
        ];

        $this->assertResponse($client->getResponse(), $expected);
    }

    public function testTheSecondPageOfTheListOfProducts()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products?attributes=a_text&page=2&limit=2');
        $expected = [
            '_links'       => [
                'self'     => ['href' => 'http://localhost/api/rest/v1/products?attributes=a_text&page=2&limit=2'],
                'first'    => ['href' => 'http://localhost/api/rest/v1/products?attributes=a_text&page=1&limit=2'],
                'last'     => ['href' => 'http://localhost/api/rest/v1/products?attributes=a_text&page=3&limit=2'],
                'previous' => ['href' => 'http://localhost/api/rest/v1/products?attributes=a_text&page=1&limit=2'],
                'next'     => ['href' => 'http://localhost/api/rest/v1/products?attributes=a_text&page=3&limit=2'],
            ],
            'current_page' => 2,
            'pages_count'  => 3,
            'items_count'  => 6,
            '_embedded'    => [
                'items' => [
                    [
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/products/scopable']
                        ],
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
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/products/localizable_and_scopable']
                        ],
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
                ]
            ]
        ];

        $this->assertResponse($client->getResponse(), $expected);
    }

    public function testOutOfRangeProductsList()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products?page=2');
        $expected = [
            '_links'       => [
                'self'     => ['href' => 'http://localhost/api/rest/v1/products?page=2&limit=10'],
                'first'    => ['href' => 'http://localhost/api/rest/v1/products?page=1&limit=10'],
                'last'     => ['href' => 'http://localhost/api/rest/v1/products?page=1&limit=10'],
            ],
            'current_page' => 2,
            'pages_count'  => 1,
            'items_count'  => 6,
            '_embedded'    => [
                'items' => []
            ]
        ];

        $this->assertResponse($client->getResponse(), $expected);
    }

    public function testListProductsWithSearch()
    {
        $client = $this->createAuthenticatedClient();

        $search = '{"a_metric":[{"operator":">","value":{"amount":"9","unit":"KILOWATT"}}]}';
        $client->request('GET', 'api/rest/v1/products?search=' . $search);
        $expected = [
            '_links'       => [
                'self'  => ['href' => 'http://localhost/api/rest/v1/products?search=' . urlencode($search) . '&page=1&limit=10'],
                'first' => ['href' => 'http://localhost/api/rest/v1/products?search=' . urlencode($search) . '&page=1&limit=10'],
                'last'  => ['href' => 'http://localhost/api/rest/v1/products?search=' . urlencode($search) . '&page=1&limit=10'],
            ],
            'current_page' => 1,
            'pages_count'  => 1,
            'items_count'  => 1,
            '_embedded'    => [
                'items' => [
                    [
                        '_links' => [
                            'self' => ['href' => 'http://localhost/api/rest/v1/products/simple']
                        ],
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
                    ]
                ]
            ]
        ];

        $this->assertResponse($client->getResponse(), $expected);
    }

    public function testListProductsWithMultiplePQBFilters()
    {
        $client = $this->createAuthenticatedClient();

        $search = '{"categories":[{"operator":"IN", "value":["categoryB"]}], "a_yes_no":[{"operator":"=","value":true}]}';
        $client->request('GET', 'api/rest/v1/products?search=' . $search);
        $expected = [
            '_links'       => [
                'self'  => ['href' => 'http://localhost/api/rest/v1/products?search=' . urlencode($search) . '&page=1&limit=10'],
                'first' => ['href' => 'http://localhost/api/rest/v1/products?search=' . urlencode($search) . '&page=1&limit=10'],
                'last'  => ['href' => 'http://localhost/api/rest/v1/products?search=' . urlencode($search) . '&page=1&limit=10'],
            ],
            'current_page' => 1,
            'pages_count'  => 1,
            'items_count'  => 0,
            '_embedded'    => [
                'items' => []
            ]
        ];

        $this->assertResponse($client->getResponse(), $expected);
    }


    /**
     * @param Response $response
     * @param array    $expected
     */
    private function assertResponse(Response $response, array $expected)
    {
        $result = json_decode($response->getContent(), true);

        foreach ($result['_embedded']['items'] as $index => $product) {
            $product = $this->sanitizeDateFields($product);
            $result['_embedded']['items'][$index] = $this->sanitizeMediaAttributeData($product);

            if (isset($expected['_embedded']['items'][$index])) {
                $expected['_embedded']['items'][$index] = $this->sanitizeDateFields($expected['_embedded']['items'][$index]);
                $expected['_embedded']['items'][$index] = $this->sanitizeMediaAttributeData($expected['_embedded']['items'][$index]);
            }
        }

        $this->assertSame($expected, $result);
    }
}
