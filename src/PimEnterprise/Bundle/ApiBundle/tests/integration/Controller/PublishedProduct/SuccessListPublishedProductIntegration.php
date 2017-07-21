<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\PublishedProduct;

use Doctrine\Common\Collections\Collection;

class SuccessListPublishedProductIntegration extends AbstractPublishedProductTestCase
{
    /** @var Collection */
    private $products;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        // no locale, no scope, 1 category
        $product1 = $this->createProduct('simple', [
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
        $product2 = $this->createProduct('localizable', [
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
        $product3 = $this->createProduct('scopable', [
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
        $product4 = $this->createProduct('localizable_and_scopable', [
            'categories' => ['categoryA', 'master_china'],
            'values'     => [
                'a_localized_and_scopable_text_area' => [
                    ['data' => 'Big description', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                    ['data' => 'Medium description', 'locale' => 'en_US', 'scope' => 'tablet'],
                    ['data' => 'Grande description', 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
                    ['data' => 'Description moyenne', 'locale' => 'fr_FR', 'scope' => 'tablet']
                ]
            ]
        ]);

        $product5 = $this->createProduct('product_china', [
            'categories' => ['master_china']
        ]);

        $product6 = $this->createProduct('product_without_category2', [
            'values' => [
                'a_yes_no' => [
                    ['data' => true, 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->publishProduct($product1);
        $this->publishProduct($product2);
        $this->publishProduct($product3);
        $this->publishProduct($product4);
        $this->publishProduct($product5);
        $this->publishProduct($product6);

        $this->products = $this->get('pim_catalog.repository.product')->findAll();
    }

    /**
     * Get all products, whatever locale, scope, category
     */
    public function testListPublishedProductsWithoutParameter()
    {
        $standardizedPublishedProducts = $this->getStandardizedPublishedProducts();
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/published-products');
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href": "http://localhost/api/rest/v1/published-products?limit=10"},
        "first" : {"href": "http://localhost/api/rest/v1/published-products?limit=10"}
    },
    "current_page" : null,
    "_embedded"    : {
		"items": [
            {$standardizedPublishedProducts['simple']},
            {$standardizedPublishedProducts['localizable']},
            {$standardizedPublishedProducts['scopable']},
            {$standardizedPublishedProducts['localizable_and_scopable']},
            {$standardizedPublishedProducts['product_china']},
            {$standardizedPublishedProducts['product_without_category2']}
		]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testFirstPageListPublishedProductsWithCount()
    {
        $standardizedProducts = $this->getStandardizedPublishedProducts();
        $client = $this->createAuthenticatedClient();

        $nextId = urlencode($this->getEncryptedId('scopable'));

        $client->request('GET', 'api/rest/v1/published-products?with_count=true&limit=3');
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href": "http://localhost/api/rest/v1/published-products?limit=3&with_count=true"},
        "first" : {"href": "http://localhost/api/rest/v1/published-products?limit=3&with_count=true"},
        "next"  : {"href": "http://localhost/api/rest/v1/published-products?limit=3&with_count=true&search_after={$nextId}"}
    },
    "current_page" : null,
    "items_count"  : 6,
    "_embedded"    : {
		"items": [
            {$standardizedProducts['simple']},
            {$standardizedProducts['localizable']},
            {$standardizedProducts['scopable']}
		]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    /**
     * Get products with "search_before" parameter.
     */
    public function testListPublishedProductsWithSearchBeforeParameter()
    {
        $standardizedProducts = $this->getStandardizedPublishedProducts();
        $client = $this->createAuthenticatedClient();

        $ids = [
            'localizable'              => urlencode($this->getEncryptedId('localizable')),
            'localizable_and_scopable' => urlencode($this->getEncryptedId('localizable_and_scopable')),
            'scopable'                 => urlencode($this->getEncryptedId('scopable')),
        ];

        $client->request('GET', sprintf('api/rest/v1/published-products?search_before=%s&limit=2', $ids['localizable_and_scopable']));
        $expected = <<<JSON
{
    "_links": {
        "self" : {"href": "http://localhost/api/rest/v1/published-products?limit=2&search_before={$ids['localizable_and_scopable']}"},
        "first" : {"href": "http://localhost/api/rest/v1/published-products?limit=2"},
        "next": {"href": "http://localhost/api/rest/v1/published-products?limit=2&search_after={$ids['scopable']}"},
        "previous": {"href": "http://localhost/api/rest/v1/published-products?limit=2&search_before={$ids['localizable']}"}
    },
    "current_page" : null,
    "_embedded"    : {
		"items": [
            {$standardizedProducts['localizable']},
            {$standardizedProducts['scopable']}
		]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    /**
     * Get the 3 latest published products with "search_before" parameter.
     */
    public function testLatestPublishedProductsWithSearchBeforeParameter()
    {
        $standardizedProducts = $this->getStandardizedPublishedProducts();
        $client = $this->createAuthenticatedClient();

        $ids = [
            'product_china'             => urlencode($this->getEncryptedId('product_china')),
            'product_without_category2' => urlencode($this->getEncryptedId('product_without_category2'))
        ];

        $client->request('GET', 'api/rest/v1/published-products?search_before=&limit=2');
        $expected = <<<JSON
{
    "_links": {
        "self" : {"href": "http://localhost/api/rest/v1/published-products?limit=2&search_before="},
        "first" : {"href": "http://localhost/api/rest/v1/published-products?limit=2"},
        "next": {"href": "http://localhost/api/rest/v1/published-products?limit=2&search_after={$ids['product_without_category2']}"},
        "previous": {"href": "http://localhost/api/rest/v1/published-products?limit=2&search_before={$ids['product_china']}"}
    },
    "current_page" : null,
    "_embedded"    : {
		"items": [
            {$standardizedProducts['product_china']},
            {$standardizedProducts['product_without_category2']}
		]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    /**
     * Scope "ecommerce" has only "en_US" activated locale and it category tree linked is "master"
     * So PV are returned only if:
     *    - scope = "ecommerce"
     *    - locale = "en_US" or null
     * Then only published products in "master" tree are returned
     */
    public function testListPublishedProductsWithEcommerceChannel()
    {
        $standardizedProducts = $this->getStandardizedPublishedProducts();
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/published-products?scope=ecommerce');
        $expected = <<<JSON
{
    "_links"       : {
        "self"  : {"href" : "http://localhost/api/rest/v1/published-products?limit=10&scope=ecommerce"},
        "first" : {"href" : "http://localhost/api/rest/v1/published-products?limit=10&scope=ecommerce"}
    },
    "current_page" : null,
    "_embedded"    : {
        "items" : [
            {$standardizedProducts['simple']},
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/published-products/localizable"}
                },
                "identifier"    : "localizable",
                "family"        : null,
                "groups"        : [],
                "variant_group" : null,
                "categories"    : ["categoryB"],
                "enabled"       : true,
                "values"        : {
                    "a_localizable_image" : [
                        {
                            "locale" : "en_US",
                            "scope" : null,
                            "data" : "8/5/6/e/856e7f47e3e53415d9c4ce8efe9bb51c8b2c68d5_akeneo.jpg",
                            "_links": {
                                "download": {
                                    "href": "http://localhost/api/rest/v1/8/5/6/e/856e7f47e3e53415d9c4ce8efe9bb51c8b2c68d5_akeneo.jpg/download"
                                }
                            }
                        }
                    ]
                },
                "created"       : "2017-01-23T11:44:25+01:00",
                "updated"       : "2017-01-23T11:44:25+01:00",
                "associations"  : {}
            },
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/published-products/scopable"}
                },
                "identifier"    : "scopable",
                "family"        : null,
                "groups"        : [],
                "variant_group" : null,
                "categories"    : ["categoryA1", "categoryA2"],
                "enabled"       : true,
                "values"        : {
                    "a_scopable_price" : [
                        {
                            "locale" : null,
                            "scope"  : "ecommerce",
                            "data"   : [
                                {"amount" : "10.50", "currency" : "EUR"},
                                {"amount" : "11.50", "currency" : "USD"},
                                {"amount" : "78.77", "currency" : "CNY"}
                            ]
                        }
                    ]
                },
                "created"       : "2017-01-23T11:44:25+01:00",
                "updated"       : "2017-01-23T11:44:25+01:00",
                "associations"  : {}
            },
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/published-products/localizable_and_scopable"}
                },
                "identifier"    : "localizable_and_scopable",
                "family"        : null,
                "groups"        : [],
                "variant_group" : null,
                "categories"    : ["categoryA", "master_china"],
                "enabled"       : true,
                "values"        : {
                    "a_localized_and_scopable_text_area" : [
                        {"locale" : "en_US", "scope" : "ecommerce", "data" : "Big description"}
                    ]
                },
                "created"       : "2017-01-23T11:44:25+01:00",
                "updated"       : "2017-01-23T11:44:25+01:00",
                "associations"  : {}
            }
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    /**
     * Scope "tablet" has "fr_FR" and "en_US" activated locales and it category tree linked is "master"
     * So PV are returned only if:
     *     - scope = "tablet"
     *     - locale = "en_US", "fr_FR" or null
     * Then only published products in "master" tree are returned
     */
    public function testListPublishedProductsWithTabletChannel()
    {
        $standardizedProducts = $this->getStandardizedPublishedProducts();
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/published-products?scope=tablet');
        $expected = <<<JSON
{
    "_links"       : {
        "self"  : {"href" : "http://localhost/api/rest/v1/published-products?limit=10&scope=tablet"},
        "first" : {"href" : "http://localhost/api/rest/v1/published-products?limit=10&scope=tablet"}
    },
    "current_page" : null,
    "_embedded"    : {
        "items" : [
            {$standardizedProducts['simple']},
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/published-products/localizable"}
                },
                "identifier"    : "localizable",
                "family"        : null,
                "groups"        : [],
                "variant_group" : null,
                "categories"    : ["categoryB"],
                "enabled"       : true,
                "values"        : {
                    "a_localizable_image" : [
                        {
                            "locale" : "en_US",
                            "scope" : null,
                            "data" : "8/5/6/e/856e7f47e3e53415d9c4ce8efe9bb51c8b2c68d5_akeneo.jpg",
                            "_links": {
                                "download": {
                                    "href": "http://localhost/api/rest/v1/8/5/6/e/856e7f47e3e53415d9c4ce8efe9bb51c8b2c68d5_akeneo.jpg/download"
                                }
                            }
                        },
                        {
                            "locale" : "fr_FR",
                            "scope" : null,
                            "data" : "5/5/9/6/559681bb0b2df7ae0eaf3bda76af5819c08bd6ae_akeneo.jpg",
                            "_links": {
                                "download": {
                                    "href": "http://localhost/api/rest/v1/5/5/9/6/559681bb0b2df7ae0eaf3bda76af5819c08bd6ae_akeneo.jpg/download"
                                }
                            }
                        }
                    ]
                },
                "created"       : "2017-01-23T11:44:25+01:00",
                "updated"       : "2017-01-23T11:44:25+01:00",
                "associations"  : {}
            },
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/published-products/scopable"}
                },
                "identifier"    : "scopable",
                "family"        : null,
                "groups"        : [],
                "variant_group" : null,
                "categories"    : ["categoryA1", "categoryA2"],
                "enabled"       : true,
                "values"        : {
                    "a_scopable_price" : [
                        {
                            "locale" : null,
                            "scope"  : "tablet",
                            "data"   : [
                                {"amount" : "10.50", "currency" : "EUR"},
                                {"amount" : "11.50", "currency" : "USD"},
                                {"amount" : "78.77", "currency" : "CNY"}
                            ]
                        }
                    ]
                },
                "created"       : "2017-01-23T11:44:25+01:00",
                "updated"       : "2017-01-23T11:44:25+01:00",
                "associations"  : {}
            },
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/published-products/localizable_and_scopable"}
                },
                "identifier"    : "localizable_and_scopable",
                "family"        : null,
                "groups"        : [],
                "variant_group" : null,
                "categories"    : ["categoryA", "master_china"],
                "enabled"       : true,
                "values"        : {
                    "a_localized_and_scopable_text_area" : [
                        {"locale" : "en_US", "scope" : "tablet", "data" : "Medium description"},
                        {"locale" : "fr_FR", "scope" : "tablet", "data" : "Description moyenne"}
                    ]
                },
                "created"       : "2017-01-23T11:44:25+01:00",
                "updated"       : "2017-01-23T11:44:25+01:00",
                "associations"  : {}
            }
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    /**
     * Filter on scope "tablet" and locale "fr_FR"
     * So PV are returned only if:
     *     - scope = "tablet"
     *     - locale = "fr_FR" or null
     * Then only published products in "master" tree are returned
     */
    public function testListPublishedProductsWithTabletChannelAndFRLocale()
    {
        $standardizedProducts = $this->getStandardizedPublishedProducts();
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/published-products?scope=tablet&locales=fr_FR');
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href" : "http://localhost/api/rest/v1/published-products?limit=10&scope=tablet&locales=fr_FR"},
        "first" : {"href" : "http://localhost/api/rest/v1/published-products?limit=10&scope=tablet&locales=fr_FR"}
    },
    "current_page" : null,
    "_embedded"    : {
        "items" : [
            {$standardizedProducts['simple']},
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/published-products/localizable"}
                },
                "identifier"    : "localizable",
                "family"        : null,
                "groups"        : [],
                "variant_group" : null,
                "categories"    : ["categoryB"],
                "enabled"       : true,
                "values"        : {
                    "a_localizable_image" : [
                        {
                            "locale" : "fr_FR",
                            "scope" : null,
                            "data" : "5/5/9/6/559681bb0b2df7ae0eaf3bda76af5819c08bd6ae_akeneo.jpg",
                            "_links": {
                                "download": {
                                    "href": "http://localhost/api/rest/v1/5/5/9/6/559681bb0b2df7ae0eaf3bda76af5819c08bd6ae_akeneo.jpg/download"
                                }
                            }
                        }
                    ]
                },
                "created"       : "2017-01-23T11:44:25+01:00",
                "updated"       : "2017-01-23T11:44:25+01:00",
                "associations"  : {}
            },
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/published-products/scopable"}
                },
                "identifier"    : "scopable",
                "family"        : null,
                "groups"        : [],
                "variant_group" : null,
                "categories"    : ["categoryA1", "categoryA2"],
                "enabled"       : true,
                "values"        : {
                    "a_scopable_price" : [
                        {
                            "locale" : null,
                            "scope"  : "tablet",
                            "data"   : [
                                {"amount" : "10.50", "currency" : "EUR"},
                                {"amount" : "11.50", "currency" : "USD"},
                                {"amount" : "78.77", "currency" : "CNY"}
                            ]
                        }
                    ]
                },
                "created"       : "2017-01-23T11:44:25+01:00",
                "updated"       : "2017-01-23T11:44:25+01:00",
                "associations"  : {}
            },
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/published-products/localizable_and_scopable"}
                },
                "identifier"    : "localizable_and_scopable",
                "family"        : null,
                "groups"        : [],
                "variant_group" : null,
                "categories"    : ["categoryA", "master_china"],
                "enabled"       : true,
                "values"        : {
                    "a_localized_and_scopable_text_area" : [
                        {"locale" : "fr_FR", "scope" : "tablet", "data" : "Description moyenne"}
                    ]
                },
                "created"       : "2017-01-23T11:44:25+01:00",
                "updated"       : "2017-01-23T11:44:25+01:00",
                "associations"  : {}
            }
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    /**
     * Filter on locales "en_US" and "fr_FR"
     * So PV are returned only if:
     *     - locale = "en_US", "fr_FR" or null
     * Then we return all published products (whatever the categories)
     */
    public function testListPublishedProductsWithENAndFRLocales()
    {
        $standardizedProducts = $this->getStandardizedPublishedProducts();
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/published-products?locales=en_US,fr_FR');
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href" : "http://localhost/api/rest/v1/published-products?limit=10&locales=en_US%2Cfr_FR"},
        "first" : {"href" : "http://localhost/api/rest/v1/published-products?limit=10&locales=en_US%2Cfr_FR"}
    },
    "current_page" : null,
    "_embedded"    : {
        "items" : [
            {$standardizedProducts['simple']},
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/published-products/localizable"}
                },
                "identifier"    : "localizable",
                "family"        : null,
                "groups"        : [],
                "variant_group" : null,
                "categories"    : ["categoryB"],
                "enabled"       : true,
                "values"        : {
                    "a_localizable_image" : [
                        {
                            "locale" : "en_US",
                            "scope" : null,
                            "data" : "8/5/6/e/856e7f47e3e53415d9c4ce8efe9bb51c8b2c68d5_akeneo.jpg",
                            "_links": {
                                "download": {
                                    "href": "http://localhost/api/rest/v1/8/5/6/e/856e7f47e3e53415d9c4ce8efe9bb51c8b2c68d5_akeneo.jpg/download"
                                }
                            }
                        },
                        {
                            "locale": "fr_FR",
                            "scope": null,
                            "data": "6\/7\/8\/3\/6783035ea95aefa68c1c0732a3ceb197319367fa_akeneo.jpg",
                            "_links": {
                                "download": {
                                    "href": "http:\/\/localhost\/api\/rest\/v1\/media-files\/6\/7\/8\/3\/6783035ea95aefa68c1c0732a3ceb197319367fa_akeneo.jpg\/download"
                                }
                            }
                        }
                    ]
                },
                "created"       : "2017-01-23T11:44:25+01:00",
                "updated"       : "2017-01-23T11:44:25+01:00",
                "associations"  : {}
            },
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/published-products/scopable"}
                },
                "identifier"    : "scopable",
                "family"        : null,
                "groups"        : [],
                "variant_group" : null,
                "categories"    : ["categoryA1", "categoryA2"],
                "enabled"       : true,
                "values"        : {
                    "a_scopable_price" : [
                        {
                            "locale" : null,
                            "scope"  : "tablet",
                            "data"   : [
                                {"amount" : "10.50", "currency" : "EUR"},
                                {"amount" : "11.50", "currency" : "USD"},
                                {"amount" : "78.77", "currency" : "CNY"}
                            ]
                        },
                        {
                            "locale" : null,
                            "scope"  : "ecommerce",
                            "data"   : [
                                {"amount" : "10.50", "currency" : "EUR"},
                                {"amount" : "11.50", "currency" : "USD"},
                                {"amount" : "78.77", "currency" : "CNY"}
                            ]
                        }
                    ]
                },
                "created"       : "2017-01-23T11:44:25+01:00",
                "updated"       : "2017-01-23T11:44:25+01:00",
                "associations"  : {}
            },
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/published-products/localizable_and_scopable"}
                },
                "identifier"    : "localizable_and_scopable",
                "family"        : null,
                "groups"        : [],
                "variant_group" : null,
                "categories"    : ["categoryA", "master_china"],
                "enabled"       : true,
                "values"        : {
                    "a_localized_and_scopable_text_area" : [
                        {"locale" : "en_US", "scope" : "tablet", "data" : "Medium description"},
                        {"locale" : "en_US", "scope" : "ecommerce", "data" : "Big description"},
                        {"locale" : "fr_FR", "scope" : "tablet", "data" : "Description moyenne"},
                        {"locale" : "fr_FR", "scope" : "ecommerce", "data" : "Grande description"}
                    ]
                },
                "created"       : "2017-01-23T11:44:25+01:00",
                "updated"       : "2017-01-23T11:44:25+01:00",
                "associations"  : []
            },
            {$standardizedProducts['product_china']},
            {$standardizedProducts['product_without_category2']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testListPublishedProductsWithFilteredAttributes()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/published-products?attributes=a_text');
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href" : "http://localhost/api/rest/v1/published-products?limit=10&attributes=a_text"},
        "first" : {"href" : "http://localhost/api/rest/v1/published-products?limit=10&attributes=a_text"}
    },
    "current_page" : null,
    "_embedded"    : {
        "items" : [
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/published-products/simple"}
                },
                "identifier"    : "simple",
                "family"        : null,
                "groups"        : [],
                "variant_group" : null,
                "categories"    : ["master"],
                "enabled"       : true,
                "values"        : {
                    "a_text" : [
                        {
                            "locale" : null,
                            "scope"  : null,
                            "data"   : "Text"
                        }
                    ]
                },
                "created"       : "2017-01-23T11:44:25+01:00",
                "updated"       : "2017-01-23T11:44:25+01:00",
                "associations"  : {}
            },
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/published-products/localizable"}
                },
                "identifier"    : "localizable",
                "family"        : null,
                "groups"        : [],
                "variant_group" : null,
                "categories"    : ["categoryB"],
                "enabled"       : true,
                "values"        : {},
                "created"       : "2017-01-23T11:44:25+01:00",
                "updated"       : "2017-01-23T11:44:25+01:00",
                "associations"  : {}
            },
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/published-products/scopable"}
                },
                "identifier"    : "scopable",
                "family"        : null,
                "groups"        : [],
                "variant_group" : null,
                "categories"    : ["categoryA1", "categoryA2"],
                "enabled"       : true,
                "values"        : {},
                "created"       : "2017-01-23T11:44:25+01:00",
                "updated"       : "2017-01-23T11:44:25+01:00",
                "associations"  : {}
            },
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/published-products/localizable_and_scopable"}
                },
                "identifier"    : "localizable_and_scopable",
                "family"        : null,
                "groups"        : [],
                "variant_group" : null,
                "categories"    : ["categoryA", "master_china"],
                "enabled"       : true,
                "values"        : {},
                "created"       : "2017-01-23T11:44:25+01:00",
                "updated"       : "2017-01-23T11:44:25+01:00",
                "associations"  : {}
            },
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/published-products/product_china"}
                },
                "identifier"    : "product_china",
                "family"        : null,
                "groups"        : [],
                "variant_group" : null,
                "categories"    : ["master_china"],
                "enabled"       : true,
                "values"        : {},
                "created"       : "2017-01-23T11:44:25+01:00",
                "updated"       : "2017-01-23T11:44:25+01:00",
                "associations"  : {}
            },
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/published-products/product_without_category2"}
                },
                "identifier"    : "product_without_category2",
                "family"        : null,
                "groups"        : [],
                "variant_group" : null,
                "categories"    : [],
                "enabled"       : true,
                "values"        : [],
                "created"       : "2017-01-23T11:44:25+01:00",
                "updated"       : "2017-01-23T11:44:25+01:00",
                "associations"  : {}
            }
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testListPublishedProductsWithChannelLocalesAndAttributesParams()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/published-products?scope=tablet&locales=fr_FR&attributes=a_scopable_price,a_metric,a_localized_and_scopable_text_area');
        $expected = <<<JSON
{
    "_links"       : {
        "self"  : {"href" : "http://localhost/api/rest/v1/published-products?limit=10&scope=tablet&locales=fr_FR&attributes=a_scopable_price%2Ca_metric%2Ca_localized_and_scopable_text_area"},
        "first" : {"href" : "http://localhost/api/rest/v1/published-products?limit=10&scope=tablet&locales=fr_FR&attributes=a_scopable_price%2Ca_metric%2Ca_localized_and_scopable_text_area"}
    },
    "current_page" : null,
    "_embedded"    : {
        "items" : [
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/published-products/simple"}
                },
                "identifier"    : "simple",
                "family"        : null,
                "groups"        : [],
                "variant_group" : null,
                "categories"    : ["master"],
                "enabled"       : true,
                "values"        : {
                    "a_metric" : [
                        {
                            "locale" : null,
                            "scope"  : null,
                            "data"   : {
                                "amount" : "10.0000",
                                "unit"   : "KILOWATT"
                            }
                        }
                    ]
                },
                "created"       : "2017-01-23T11:44:25+01:00",
                "updated"       : "2017-01-23T11:44:25+01:00",
                "associations"  : {}
            },
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/published-products/localizable"}
                },
                "identifier"    : "localizable",
                "family"        : null,
                "groups"        : [],
                "variant_group" : null,
                "categories"    : ["categoryB"],
                "enabled"       : true,
                "values"        : [],
                "created"       : "2017-01-23T11:44:25+01:00",
                "updated"       : "2017-01-23T11:44:25+01:00",
                "associations"  : {}
            },
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/published-products/scopable"}
                },
                "identifier"    : "scopable",
                "family"        : null,
                "groups"        : [],
                "variant_group" : null,
                "categories"    : ["categoryA1", "categoryA2"],
                "enabled"       : true,
                "values"        : {
                    "a_scopable_price" : [
                        {
                            "locale" : null,
                            "scope"  : "tablet",
                            "data"   : [
                                {"amount" : "10.50", "currency" : "EUR"},
                                {"amount" : "11.50", "currency" : "USD"},
                                {"amount" : "78.77", "currency" : "CNY"}
                            ]
                        }
                    ]
                },
                "created"       : "2017-01-23T11:44:25+01:00",
                "updated"       : "2017-01-23T11:44:25+01:00",
                "associations"  : {}
            },
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/published-products/localizable_and_scopable"}
                },
                "identifier"    : "localizable_and_scopable",
                "family"        : null,
                "groups"        : [],
                "variant_group" : null,
                "categories"    : ["categoryA", "master_china"],
                "enabled"       : true,
                "values"        : {
                    "a_localized_and_scopable_text_area" : [
                        {"locale" : "fr_FR", "scope" : "tablet", "data" : "Description moyenne"}
                    ]
                },
                "created"       : "2017-01-23T11:44:25+01:00",
                "updated"       : "2017-01-23T11:44:25+01:00",
                "associations"  : {}
            }
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testTheSecondPageOfTheListOfPublishedProductsWithoutCount()
    {
        $client = $this->createAuthenticatedClient();

        $ids = [
            'localizable_and_scopable' => urlencode($this->getEncryptedId('localizable_and_scopable')),
            'product_china'            => urlencode($this->getEncryptedId('product_china')),
            'product_without_category2' => urlencode($this->getEncryptedId('product_without_category2')),
        ];

        $client->request('GET', sprintf('api/rest/v1/published-products?attributes=a_text&search_after=%s&limit=2&with_count=false', $ids['localizable_and_scopable']));
        $expected = <<<JSON
{
    "_links"       : {
        "self"     : {"href" : "http://localhost/api/rest/v1/published-products?limit=2&attributes=a_text&search_after={$ids['localizable_and_scopable']}&with_count=false"},
        "first"    : {"href" : "http://localhost/api/rest/v1/published-products?limit=2&attributes=a_text&with_count=false"},
        "previous" : {"href" : "http://localhost/api/rest/v1/published-products?limit=2&attributes=a_text&with_count=false&search_before={$ids['product_china']}"},
        "next"     : {"href" : "http://localhost/api/rest/v1/published-products?limit=2&attributes=a_text&search_after={$ids['product_without_category2']}&with_count=false"}
    },
    "current_page" : null,
    "_embedded"    : {
        "items" : [
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/published-products/product_china"}
                },
                "identifier"    : "product_china",
                "family"        : null,
                "groups"        : [],
                "variant_group" : null,
                "categories"    : ["master_china"],
                "enabled"       : true,
                "values"        : {},
                "created"       : "2017-01-23T11:44:25+01:00",
                "updated"       : "2017-01-23T11:44:25+01:00",
                "associations"  : {}
            },
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/published-products/product_without_category2"}
                },
                "identifier"    : "product_without_category2",
                "family"        : null,
                "groups"        : [],
                "variant_group" : null,
                "categories"    : [],
                "enabled"       : true,
                "values"        : {},
                "created"       : "2017-01-23T11:44:25+01:00",
                "updated"       : "2017-01-23T11:44:25+01:00",
                "associations"  : {}
            }
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testOutOfRangePublishedProductsList()
    {
        $client = $this->createAuthenticatedClient();

        $id = urlencode($this->getEncryptedId('product_without_category2'));

        $client->request('GET', sprintf('api/rest/v1/published-products?search_after=%s&with_count=true', $id));
        $expected = <<<JSON
{
    "_links" : {
        "self": {"href" : "http://localhost/api/rest/v1/published-products?limit=10&search_after={$id}&with_count=true"},
        "first" : {"href" : "http://localhost/api/rest/v1/published-products?limit=10&with_count=true"},
        "previous": {"href" : "http://localhost/api/rest/v1/published-products?limit=10&with_count=true&search_before="}
    },
    "current_page" : null,
    "items_count"  : 6,
    "_embedded"    : {
        "items" : []
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testListPublishedProductsWithSearch()
    {
        $client = $this->createAuthenticatedClient();

        $search = '{"a_metric":[{"operator":">","value":{"amount":"9","unit":"KILOWATT"}}]}';
        $client->request('GET', 'api/rest/v1/published-products?search=' . $search);
        $searchEncoded = urlencode($search);
        $expected = <<<JSON
{
    "_links"       : {
        "self"  : {"href" : "http://localhost/api/rest/v1/published-products?limit=10&search=${searchEncoded}"},
        "first" : {"href" : "http://localhost/api/rest/v1/published-products?limit=10&search=${searchEncoded}"}
    },
    "current_page" : null,
    "_embedded"    : {
        "items" : [
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/published-products/simple"}
                },
                "identifier"    : "simple",
                "family"        : null,
                "groups"        : [],
                "variant_group" : null,
                "categories"    : ["master"],
                "enabled"       : true,
                "values"        : {
                    "a_metric" : [
                        {
                            "locale" : null,
                            "scope"  : null,
                            "data"   : {
                                "amount" : "10.0000",
                                "unit"   : "KILOWATT"
                            }
                        }
                    ],
                    "a_text" : [
                        {
                            "locale" : null,
                            "scope"  : null,
                            "data"   : "Text"
                        }
                    ]
                },
                "created"       : "2017-01-23T11:44:25+01:00",
                "updated"       : "2017-01-23T11:44:25+01:00",
                "associations"  : {}
            }
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testListPublishedProductsWithMultiplePQBFilters()
    {
        $client = $this->createAuthenticatedClient();

        $search = '{"categories":[{"operator":"IN", "value":["categoryB"]}], "a_yes_no":[{"operator":"=","value":true}]}';
        $client->request('GET', 'api/rest/v1/published-products?search=' . $search);
        $searchEncoded = urlencode($search);
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href" : "http://localhost/api/rest/v1/published-products?limit=10&search=${searchEncoded}"},
        "first" : {"href" : "http://localhost/api/rest/v1/published-products?limit=10&search=${searchEncoded}"}
    },
    "current_page" : null,
    "_embedded"    : {
        "items" : []
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testListPublishedProductsWithCompletenessPQBFilters()
    {
        $client = $this->createAuthenticatedClient();

        $search = '{"completeness":[{"operator":"GREATER THAN ON ALL LOCALES","value":50,"locales":["fr_FR"],"scope":"ecommerce"}],"categories":[{"operator":"IN", "value":["categoryB"]}], "a_yes_no":[{"operator":"=","value":true}]}';
        $client->request('GET', 'api/rest/v1/published-products?search=' . $search);
        $searchEncoded = urlencode($search);
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href" : "http://localhost/api/rest/v1/published-products?limit=10&search=${searchEncoded}"},
        "first" : {"href" : "http://localhost/api/rest/v1/published-products?limit=10&search=${searchEncoded}"}
    },
    "current_page" : null,
    "_embedded"    : {
        "items" : []
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testPaginationLastPageOfTheListOfPublishedProducts()
    {
        $standardizedProducts = $this->getStandardizedPublishedProducts();
        $client = $this->createAuthenticatedClient();

        $ids = [
            'localizable_and_scopable' => urlencode($this->getEncryptedId('localizable_and_scopable')),
            'product_china'            => urlencode($this->getEncryptedId('product_china')),
        ];

        $client->request('GET', sprintf('api/rest/v1/published-products?limit=4&search_after=%s', $ids['localizable_and_scopable']));
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href": "http://localhost/api/rest/v1/published-products?limit=4&search_after={$ids['localizable_and_scopable']}"},
        "first" : {"href": "http://localhost/api/rest/v1/published-products?limit=4"},
        "previous": {"href": "http://localhost/api/rest/v1/published-products?limit=4&search_before={$ids['product_china']}"}
    },
    "current_page" : null,
    "_embedded"    : {
        "items" : [
            {$standardizedProducts['product_china']},
            {$standardizedProducts['product_without_category2']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    /**
     * @group todo
     */
    public function testSearchAfterPaginationListPublishedProductsWithNextLink()
    {
        $standardizedProducts = $this->getStandardizedPublishedProducts();
        $client = $this->createAuthenticatedClient();

        $id = [
            'simple'                   => urlencode($this->getEncryptedId('simple')),
            'localizable'              => urlencode($this->getEncryptedId('localizable')),
            'localizable_and_scopable' => urlencode($this->getEncryptedId('localizable_and_scopable')),
        ];

        $client->request('GET', sprintf('api/rest/v1/published-products?pagination_type=search_after&limit=3&search_after=%s', $id['simple']));
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href": "http://localhost/api/rest/v1/published-products?limit=3&pagination_type=search_after&search_after={$id['simple']}"},
        "first" : {"href": "http://localhost/api/rest/v1/published-products?limit=3&pagination_type=search_after"},
        "next"  : {"href": "http://localhost/api/rest/v1/published-products?limit=3&pagination_type=search_after&search_after={$id['localizable_and_scopable']}"},
        "previous" : {"href": "http://localhost/api/rest/v1/published-products?limit=3&pagination_type=search_after&search_before={$id['localizable']}"}
    },
    "_embedded"    : {
        "items" : [
            {$standardizedProducts['localizable']},
            {$standardizedProducts['scopable']},
            {$standardizedProducts['localizable_and_scopable']}
        ]
    },
    "current_page": null
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testSearchAfterPaginationLastPageOfTheListOfPublishedProducts()
    {
        $standardizedProducts = $this->getStandardizedPublishedProducts();
        $client = $this->createAuthenticatedClient();

        $scopableEncryptedId = urlencode($this->getEncryptedId('scopable'));
        $localizableAndScopableEncryptedId = urlencode($this->getEncryptedId('localizable_and_scopable'));

        $client->request('GET', sprintf('api/rest/v1/published-products?pagination_type=search_after&limit=4&search_after=%s' , $scopableEncryptedId));
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href": "http://localhost/api/rest/v1/published-products?limit=4&pagination_type=search_after&search_after={$scopableEncryptedId}"},
        "first" : {"href": "http://localhost/api/rest/v1/published-products?limit=4&pagination_type=search_after"},
        "previous"  : {"href": "http://localhost/api/rest/v1/published-products?limit=4&pagination_type=search_after&search_before={$localizableAndScopableEncryptedId}"}
    },
    "_embedded"    : {
        "items" : [
            {$standardizedProducts['localizable_and_scopable']},
            {$standardizedProducts['product_china']},
            {$standardizedProducts['product_without_category2']}
        ]
    },
    "current_page": null
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    /**
     * @return array
     */
    protected function getStandardizedPublishedProducts()
    {
        $standardizedPublishedProducts['simple'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/published-products/simple"
        }
    },
    "identifier": "simple",
    "family": null,
    "groups": [],
    "variant_group": null,
    "categories": ["master"],
    "enabled": true,
    "values": {
        "a_metric": [{
            "locale": null,
            "scope": null,
            "data": {
                "amount": "10.0000",
                "unit": "KILOWATT"
            }
        }],
        "a_text": [{
            "locale": null,
            "scope": null,
            "data": "Text"
        }]
    },
    "created": "2017-03-11T10:39:38+01:00",
    "updated": "2017-03-11T10:39:38+01:00",
    "associations": {}
}
JSON;

        $standardizedPublishedProducts['localizable'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/published-products/localizable"
        }
    },
    "identifier": "localizable",
    "family": null,
    "groups": [],
    "variant_group": null,
    "categories": ["categoryB"],
    "enabled": true,
    "values": {
        "a_localizable_image": [{
            "locale": "en_US",
            "scope": null,
            "data": "7\/1\/e\/c\/71ec99d718e277bd6ec86023cbe2f02dd54218b4_akeneo.jpg",
            "_links": {
                "download": {
                    "href": "http://localhost/api/rest/v1/media-files/c/2/0/f/c20f1a4b3e6515d5676e89d52fb9e25fa1d29bd8_akeneo.jpg/download"
                }
            }
        }, {
            "locale": "fr_FR",
            "scope": null,
            "data": "6\/7\/8\/3\/6783035ea95aefa68c1c0732a3ceb197319367fa_akeneo.jpg",
            "_links": {
                "download": {
                    "href": "http:\/\/localhost\/api\/rest\/v1\/media-files\/6\/7\/8\/3\/6783035ea95aefa68c1c0732a3ceb197319367fa_akeneo.jpg\/download"
                }
            }
        }]
    },
    "created": "2017-03-11T10:39:38+01:00",
    "updated": "2017-03-11T10:39:38+01:00",
    "associations": {}
}
JSON;

        $standardizedPublishedProducts['scopable'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/published-products/scopable"
        }
    },
    "identifier": "scopable",
    "family": null,
    "groups": [],
    "variant_group": null,
    "categories": ["categoryA1", "categoryA2"],
    "enabled": true,
    "values": {
        "a_scopable_price": [{
            "locale": null,
            "scope": "tablet",
            "data": [{
                "amount": "10.50",
                "currency": "EUR"
            }, {
                "amount": "11.50",
                "currency": "USD"
            }, {
                "amount": "78.77",
                "currency": "CNY"
            }]
        }, {
            "locale": null,
            "scope": "ecommerce",
            "data": [{
                "amount": "10.50",
                "currency": "EUR"
            }, {
                "amount": "11.50",
                "currency": "USD"
            }, {
                "amount": "78.77",
                "currency": "CNY"
            }]
        }]
    },
    "created": "2017-03-11T10:39:38+01:00",
    "updated": "2017-03-11T10:39:38+01:00",
    "associations": {}
}
JSON;

        $standardizedPublishedProducts['localizable_and_scopable'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/published-products/localizable_and_scopable"
        }
    },
    "identifier": "localizable_and_scopable",
    "family": null,
    "groups": [],
    "variant_group": null,
    "categories": ["categoryA", "master_china"],
    "enabled": true,
    "values": {
        "a_localized_and_scopable_text_area": [{
            "locale": "en_US",
            "scope": "tablet",
            "data": "Medium description"
        }, {
            "locale": "fr_FR",
            "scope": "tablet",
            "data": "Description moyenne"
        }, {
            "locale": "en_US",
            "scope": "ecommerce",
            "data": "Big description"
        }, {
            "locale": "fr_FR",
            "scope": "ecommerce",
            "data": "Grande description"
        }]
    },
    "created": "2017-03-11T10:39:38+01:00",
    "updated": "2017-03-11T10:39:38+01:00",
    "associations": {}
}
JSON;

        $standardizedPublishedProducts['product_china'] = <<<JSON
{
   "_links": {
       "self": {
           "href": "http://localhost/api/rest/v1/published-products/product_china"
       }
   },
   "identifier": "product_china",
   "family": null,
   "groups": [],
   "variant_group": null,
   "categories": ["master_china"],
   "enabled": true,
   "values": {},
   "created": "2017-03-11T10:39:38+01:00",
   "updated": "2017-03-11T10:39:38+01:00",
   "associations": {}
}
JSON;

        $standardizedPublishedProducts['product_without_category2'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/published-products/product_without_category2"
        }
    },
    "identifier": "product_without_category2",
    "family": null,
    "groups": [],
    "variant_group": null,
    "categories": [],
    "enabled": true,
    "values": {
        "a_yes_no": [{
            "locale": null,
            "scope": null,
            "data": true
        }]
    },
    "created": "2017-03-11T10:39:38+01:00",
    "updated": "2017-03-11T10:39:38+01:00",
    "associations": {}
}
JSON;

        return $standardizedPublishedProducts;
    }
}
