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
                    ['data' => $this->getFixturePath('akeneo.jpg'), 'locale' => 'de_DE', 'scope' => null]
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
                            ['amount' => '78.77', 'currency' => 'CNY'],
                            ['amount' => '10.50', 'currency' => 'EUR'],
                            ['amount' => '11.50', 'currency' => 'USD'],
                        ]
                    ],
                    [
                        'locale' => null,
                        'scope'  => 'tablet',
                        'data'   => [
                            ['amount' => '78.77', 'currency' => 'CNY'],
                            ['amount' => '10.50', 'currency' => 'EUR'],
                            ['amount' => '11.50', 'currency' => 'USD'],
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
                    ['data' => 'Description moyenne', 'locale' => 'fr_FR', 'scope' => 'tablet'],
                    ['data' => 'China description', 'locale' => 'zh_CN', 'scope' => 'ecommerce_china'],
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
     * Get all published products, whatever locale, scope, category with the default pagination type that is with an offset.
     */
    public function testDefaultPaginationListPublishedProductsWithoutParameter()
    {
        $standardizedPublishedProducts = $this->getStandardizedPublishedProducts();
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/published-products');
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href": "http://localhost/api/rest/v1/published-products?page=1&with_count=false&pagination_type=page&limit=10"},
        "first" : {"href": "http://localhost/api/rest/v1/published-products?page=1&with_count=false&pagination_type=page&limit=10"}
    },
    "current_page" : 1,
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

    public function testDefaultPaginationFirstPageListPublishedProductsWithCount()
    {
        $standardizedPublishedProducts = $this->getStandardizedPublishedProducts();
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/published-products?with_count=true&limit=3');
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href": "http://localhost/api/rest/v1/published-products?page=1&with_count=true&pagination_type=page&limit=3"},
        "first" : {"href": "http://localhost/api/rest/v1/published-products?page=1&with_count=true&pagination_type=page&limit=3"},
        "next"  : {"href": "http://localhost/api/rest/v1/published-products?page=2&with_count=true&pagination_type=page&limit=3"}
    },
    "current_page" : 1,
    "items_count"  : 6,
    "_embedded"    : {
		"items": [
            {$standardizedPublishedProducts['simple']},
            {$standardizedPublishedProducts['localizable']},
            {$standardizedPublishedProducts['scopable']}
		]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testDefaultPaginationLastPageListPublishedProductsWithCount()
    {
        $standardizedPublishedProducts = $this->getStandardizedPublishedProducts();
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/published-products?with_count=true&limit=3&page=2');
        $expected = <<<JSON
{
    "_links": {
        "self"     : {"href": "http://localhost/api/rest/v1/published-products?page=2&with_count=true&pagination_type=page&limit=3"},
        "first"    : {"href": "http://localhost/api/rest/v1/published-products?page=1&with_count=true&pagination_type=page&limit=3"},
        "previous" : {"href": "http://localhost/api/rest/v1/published-products?page=1&with_count=true&pagination_type=page&limit=3"},
        "next"     : {"href": "http://localhost/api/rest/v1/published-products?page=3&with_count=true&pagination_type=page&limit=3"}
    },
    "current_page" : 2,
    "items_count"  : 6,
    "_embedded"    : {
		"items": [
            {$standardizedPublishedProducts['localizable_and_scopable']},
            {$standardizedPublishedProducts['product_china']},
            {$standardizedPublishedProducts['product_without_category2']}
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
    public function testOffsetPaginationListPublishedProductsWithEcommerceChannel()
    {
        $standardizedPublishedProducts = $this->getStandardizedPublishedProducts();
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/published-products?scope=ecommerce&pagination_type=page');
        $expected = <<<JSON
{
    "_links"       : {
        "self"  : {"href" : "http://localhost/api/rest/v1/published-products?page=1&with_count=false&pagination_type=page&limit=10&scope=ecommerce"},
        "first" : {"href" : "http://localhost/api/rest/v1/published-products?page=1&with_count=false&pagination_type=page&limit=10&scope=ecommerce"}
    },
    "current_page" : 1,
    "_embedded"    : {
        "items" : [
            {$standardizedPublishedProducts['simple']},
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/published-products/localizable"}
                },
                "identifier"    : "localizable",
                "family"        : null,
                "groups"        : [],
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
                "categories"    : ["categoryA1", "categoryA2"],
                "enabled"       : true,
                "values"        : {
                    "a_scopable_price" : [
                        {
                            "locale" : null,
                            "scope"  : "ecommerce",
                            "data"   : [
                                {"amount" : "78.77", "currency" : "CNY"},
                                {"amount" : "10.50", "currency" : "EUR"},
                                {"amount" : "11.50", "currency" : "USD"}
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
    public function testOffsetPaginationListPublishedProductsWithTabletChannel()
    {
        $standardizedPublishedProducts = $this->getStandardizedPublishedProducts();
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/published-products?scope=tablet&pagination_type=page');
        $expected = <<<JSON
{
    "_links"       : {
        "self"  : {"href" : "http://localhost/api/rest/v1/published-products?page=1&with_count=false&pagination_type=page&limit=10&scope=tablet"},
        "first" : {"href" : "http://localhost/api/rest/v1/published-products?page=1&with_count=false&pagination_type=page&limit=10&scope=tablet"}
    },
    "current_page" : 1,
    "_embedded"    : {
        "items" : [
            {$standardizedPublishedProducts['simple']},
            {$standardizedPublishedProducts['localizable']},
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/published-products/scopable"}
                },
                "identifier"    : "scopable",
                "family"        : null,
                "groups"        : [],
                "categories"    : ["categoryA1", "categoryA2"],
                "enabled"       : true,
                "values"        : {
                    "a_scopable_price" : [
                        {
                            "locale" : null,
                            "scope"  : "tablet",
                            "data"   : [
                                {"amount" : "78.77", "currency" : "CNY"},
                                {"amount" : "10.50", "currency" : "EUR"},
                                {"amount" : "11.50", "currency" : "USD"}
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
     * Then only products in "master" tree are returned
     */
    public function testOffsetPaginationListProductsWithTabletChannelAndFRLocale()
    {
        $standardizedPublishedProducts = $this->getStandardizedPublishedProducts();
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/published-products?scope=tablet&locales=fr_FR&pagination_type=page');
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href" : "http://localhost/api/rest/v1/published-products?page=1&with_count=false&pagination_type=page&limit=10&scope=tablet&locales=fr_FR"},
        "first" : {"href" : "http://localhost/api/rest/v1/published-products?page=1&with_count=false&pagination_type=page&limit=10&scope=tablet&locales=fr_FR"}
    },
    "current_page" : 1,
    "_embedded"    : {
        "items" : [
            {$standardizedPublishedProducts['simple']},
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/published-products/localizable"}
                },
                "identifier"    : "localizable",
                "family"        : null,
                "groups"        : [],
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
                "categories"    : ["categoryA1", "categoryA2"],
                "enabled"       : true,
                "values"        : {
                    "a_scopable_price" : [
                        {
                            "locale" : null,
                            "scope"  : "tablet",
                            "data"   : [
                                {"amount" : "78.77", "currency" : "CNY"},
                                {"amount" : "10.50", "currency" : "EUR"},
                                {"amount" : "11.50", "currency" : "USD"}
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
     * Scope "ecommerce_china" has "CNY" activated locale and it category tree linked is "master_china"
     * So PV are returned only if:
     *     - scope = "ecommerce_china"
     *     - locale = "en_US", "zh_CN" or null
     * Then only published products in "master_china" tree are returned
     */
    public function testOffsetPaginationListPublishedProductsWithEcommerceChinaChannel()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/published-products?scope=ecommerce_china&pagination_type=page');
        $expected = <<<JSON
{
    "_links"       : {
        "self"  : {"href" : "http://localhost/api/rest/v1/published-products?page=1&with_count=false&pagination_type=page&limit=10&scope=ecommerce_china"},
        "first" : {"href" : "http://localhost/api/rest/v1/published-products?page=1&with_count=false&pagination_type=page&limit=10&scope=ecommerce_china"}
    },
    "current_page" : 1,
    "_embedded"    : {
        "items" : [
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/published-products/localizable_and_scopable"}
                },
                "identifier"    : "localizable_and_scopable",
                "family"        : null,
                "groups"        : [],
                "categories"    : ["categoryA", "master_china"],
                "enabled"       : true,
                "values"        : {
                    "a_localized_and_scopable_text_area" : [
                        {"locale" : "zh_CN", "scope" : "ecommerce_china", "data" : "China description"}
                    ]
                },
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
                "categories"    : ["master_china"],
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

    /**
     * Filter on locales "en_US" and "zh_CN"
     * So PV are returned only if:
     *     - locale = "en_US", "zh_CN" or null
     * Then we return all products (whatever the categories)
     */
    public function testOffsetPaginationListProductsWithENAndCNLocales()
    {
        $standardizedPublishedProducts = $this->getStandardizedPublishedProducts();
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/published-products?locales=en_US,zh_CN&pagination_type=page');
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href" : "http://localhost/api/rest/v1/published-products?page=1&with_count=false&pagination_type=page&limit=10&locales=en_US%2Czh_CN"},
        "first" : {"href" : "http://localhost/api/rest/v1/published-products?page=1&with_count=false&pagination_type=page&limit=10&locales=en_US%2Czh_CN"}
    },
    "current_page" : 1,
    "_embedded"    : {
        "items" : [
            {$standardizedPublishedProducts['simple']},
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/published-products/scopable"}
                },
                "identifier"    : "scopable",
                "family"        : null,
                "groups"        : [],
                "categories"    : ["categoryA1", "categoryA2"],
                "enabled"       : true,
                "values"        : {
                    "a_scopable_price" : [
                        {
                            "locale" : null,
                            "scope"  : "tablet",
                            "data"   : [
                                {"amount" : "78.77", "currency" : "CNY"},
                                {"amount" : "10.50", "currency" : "EUR"},
                                {"amount" : "11.50", "currency" : "USD"}
                            ]
                        },
                        {
                            "locale" : null,
                            "scope"  : "ecommerce",
                            "data"   : [
                                {"amount" : "78.77", "currency" : "CNY"},
                                {"amount" : "10.50", "currency" : "EUR"},
                                {"amount" : "11.50", "currency" : "USD"}
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
                "categories"    : ["categoryA", "master_china"],
                "enabled"       : true,
                "values"        : {
                    "a_localized_and_scopable_text_area" : [
                        {"locale" : "en_US", "scope" : "tablet", "data" : "Medium description"},
                        {"locale" : "en_US", "scope" : "ecommerce", "data" : "Big description"},
                        {"locale" : "zh_CN", "scope" : "ecommerce_china", "data" : "China description"}
                    ]
                },
                "created"       : "2017-01-23T11:44:25+01:00",
                "updated"       : "2017-01-23T11:44:25+01:00",
                "associations"  : []
            },
            {$standardizedPublishedProducts['product_china']},
            {$standardizedPublishedProducts['product_without_category2']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testOffsetPaginationListProductsWithFilteredAttributes()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/published-products?attributes=a_text&pagination_type=page');
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href" : "http://localhost/api/rest/v1/published-products?page=1&with_count=false&pagination_type=page&limit=10&attributes=a_text"},
        "first" : {"href" : "http://localhost/api/rest/v1/published-products?page=1&with_count=false&pagination_type=page&limit=10&attributes=a_text"}
    },
    "current_page" : 1,
    "_embedded"    : {
        "items" : [
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/published-products/simple"}
                },
                "identifier"    : "simple",
                "family"        : null,
                "groups"        : [],
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

    public function testOffsetPaginationListProductsWithChannelLocalesAndAttributesParams()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/published-products?scope=tablet&locales=fr_FR&attributes=a_scopable_price,a_metric,a_localized_and_scopable_text_area&pagination_type=page');
        $expected = <<<JSON
{
    "_links"       : {
        "self"  : {"href" : "http://localhost/api/rest/v1/published-products?page=1&with_count=false&pagination_type=page&limit=10&scope=tablet&locales=fr_FR&attributes=a_scopable_price%2Ca_metric%2Ca_localized_and_scopable_text_area"},
        "first" : {"href" : "http://localhost/api/rest/v1/published-products?page=1&with_count=false&pagination_type=page&limit=10&scope=tablet&locales=fr_FR&attributes=a_scopable_price%2Ca_metric%2Ca_localized_and_scopable_text_area"}
    },
    "current_page" : 1,
    "_embedded"    : {
        "items" : [
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/published-products/simple"}
                },
                "identifier"    : "simple",
                "family"        : null,
                "groups"        : [],
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
                "categories"    : ["categoryA1", "categoryA2"],
                "enabled"       : true,
                "values"        : {
                    "a_scopable_price" : [
                        {
                            "locale" : null,
                            "scope"  : "tablet",
                            "data"   : [
                                {"amount" : "78.77", "currency" : "CNY"},
                                {"amount" : "10.50", "currency" : "EUR"},
                                {"amount" : "11.50", "currency" : "USD"}
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

    public function testTheSecondPageOfTheListOfProductsWithOffsetPaginationWithoutCount()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/published-products?attributes=a_text&page=2&limit=2&pagination_type=page&with_count=false');
        $expected = <<<JSON
{
    "_links"       : {
        "self"     : {"href" : "http://localhost/api/rest/v1/published-products?page=2&with_count=false&pagination_type=page&limit=2&attributes=a_text"},
        "first"    : {"href" : "http://localhost/api/rest/v1/published-products?page=1&with_count=false&pagination_type=page&limit=2&attributes=a_text"},
        "previous" : {"href" : "http://localhost/api/rest/v1/published-products?page=1&with_count=false&pagination_type=page&limit=2&attributes=a_text"},
        "next"     : {"href" : "http://localhost/api/rest/v1/published-products?page=3&with_count=false&pagination_type=page&limit=2&attributes=a_text"}
    },
    "current_page" : 2,
    "_embedded"    : {
        "items" : [
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/published-products/scopable"}
                },
                "identifier"    : "scopable",
                "family"        : null,
                "groups"        : [],
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
                "categories"    : ["categoryA", "master_china"],
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

    public function testOutOfRangeProductsList()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/published-products?page=2&pagination_type=page&with_count=true');
        $expected = <<<JSON
{
    "_links"       : {
        "self"        : {"href" : "http://localhost/api/rest/v1/published-products?page=2&with_count=true&pagination_type=page&limit=10"},
        "first"       : {"href" : "http://localhost/api/rest/v1/published-products?page=1&with_count=true&pagination_type=page&limit=10"},
        "previous"    : {"href" : "http://localhost/api/rest/v1/published-products?page=1&with_count=true&pagination_type=page&limit=10"}
    },
    "current_page" : 2,
    "items_count"  : 6,
    "_embedded"    : {
        "items" : []
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testOffsetPaginationListProductsWithSearch()
    {
        $client = $this->createAuthenticatedClient();

        $search = '{"a_metric":[{"operator":">","value":{"amount":"9","unit":"KILOWATT"}}]}';
        $client->request('GET', 'api/rest/v1/published-products?pagination_type=page&search=' . $search);
        $searchEncoded = rawurlencode($search);
        $expected = <<<JSON
{
    "_links"       : {
        "self"  : {"href" : "http://localhost/api/rest/v1/published-products?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"},
        "first" : {"href" : "http://localhost/api/rest/v1/published-products?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"}
    },
    "current_page" : 1,
    "_embedded"    : {
        "items" : [
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/published-products/simple"}
                },
                "identifier"    : "simple",
                "family"        : null,
                "groups"        : [],
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

    public function testOffsetPaginationListProductsWithMultiplePQBFilters()
    {
        $client = $this->createAuthenticatedClient();

        $search = '{"categories":[{"operator":"IN", "value":["categoryB"]}], "a_yes_no":[{"operator":"=","value":true}]}';
        $client->request('GET', 'api/rest/v1/published-products?pagination_type=page&search=' . $search);
        $searchEncoded = rawurlencode($search);
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href" : "http://localhost/api/rest/v1/published-products?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"},
        "first" : {"href" : "http://localhost/api/rest/v1/published-products?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"}
    },
    "current_page" : 1,
    "_embedded"    : {
        "items" : []
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testListProductsWithCompletenessPQBFilters()
    {
        $client = $this->createAuthenticatedClient();

        $search = '{"completeness":[{"operator":"GREATER THAN ON ALL LOCALES","value":50,"locales":["fr_FR"],"scope":"ecommerce"}],"categories":[{"operator":"IN", "value":["categoryB"]}], "a_yes_no":[{"operator":"=","value":true}]}';
        $client->request('GET', 'api/rest/v1/published-products?search=' . $search);
        $searchEncoded = rawurlencode($search);
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href" : "http://localhost/api/rest/v1/published-products?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"},
        "first" : {"href" : "http://localhost/api/rest/v1/published-products?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"}
    },
    "current_page" : 1,
    "_embedded"    : {
        "items" : []
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    /**
     * Get all products, whatever locale, scope, category with a search after pagination
     */
    public function testSearchAfterPaginationListProductsWithoutParameter()
    {
        $standardizedPublishedProducts = $this->getStandardizedPublishedProducts();
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/published-products?pagination_type=search_after');
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href": "http://localhost/api/rest/v1/published-products?pagination_type=search_after&limit=10"},
        "first" : {"href": "http://localhost/api/rest/v1/published-products?pagination_type=search_after&limit=10"}
    },
    "_embedded" : {
        "items" : [
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

    public function testSearchAfterPaginationListProductsWithNextLink()
    {
        $standardizedPublishedProducts = $this->getStandardizedPublishedProducts();
        $client = $this->createAuthenticatedClient();

        $id = [
            'simple'                   => rawurlencode($this->getEncryptedId('simple')),
            'localizable'              => rawurlencode($this->getEncryptedId('localizable')),
            'localizable_and_scopable' => rawurlencode($this->getEncryptedId('localizable_and_scopable')),
        ];

        $client->request('GET', sprintf('api/rest/v1/published-products?pagination_type=search_after&limit=3&search_after=%s', $id['simple']));
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href": "http://localhost/api/rest/v1/published-products?pagination_type=search_after&limit=3&search_after={$id['simple']}"},
        "first" : {"href": "http://localhost/api/rest/v1/published-products?pagination_type=search_after&limit=3"},
        "next"  : {"href": "http://localhost/api/rest/v1/published-products?pagination_type=search_after&limit=3&search_after={$id['localizable_and_scopable']}"}
    },
    "_embedded"    : {
        "items" : [
            {$standardizedPublishedProducts['localizable']},
            {$standardizedPublishedProducts['scopable']},
            {$standardizedPublishedProducts['localizable_and_scopable']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testSearchAfterPaginationLastPageOfTheListOfProducts()
    {
        $standardizedPublishedProducts = $this->getStandardizedPublishedProducts();
        $client = $this->createAuthenticatedClient();

        $scopableEncryptedId = rawurlencode($this->getEncryptedId('scopable'));

        $client->request('GET', sprintf('api/rest/v1/published-products?pagination_type=search_after&limit=4&search_after=%s' , $scopableEncryptedId));
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href": "http://localhost/api/rest/v1/published-products?pagination_type=search_after&limit=4&search_after={$scopableEncryptedId}"},
        "first" : {"href": "http://localhost/api/rest/v1/published-products?pagination_type=search_after&limit=4"}
    },
    "_embedded"    : {
        "items" : [
            {$standardizedPublishedProducts['localizable_and_scopable']},
            {$standardizedPublishedProducts['product_china']},
            {$standardizedPublishedProducts['product_without_category2']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    /**
     * @return array
     */
    protected function getStandardizedPublishedProducts() : array
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
    "categories": ["master"],
    "enabled": true,
    "values": {
        "a_text": [{
            "locale": null,
            "scope": null,
            "data": "Text"
        }],
        "a_metric": [{
            "locale": null,
            "scope": null,
            "data": {
                "amount": "10.0000",
                "unit": "KILOWATT"
            }
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
    "categories": ["categoryB"],
    "enabled": true,
    "values": {
        "a_localizable_image": [{
            "locale": "de_DE",
            "scope": null,
            "data": "8/8/c/2/88c252d871f39e9b7a8d02f7bdc8f810d5faca1a_akeneo.jpg",
            "_links": {
                "download": {
                    "href": "http://localhost/api/rest/v1/media-files/8/8/c/2/88c252d871f39e9b7a8d02f7bdc8f810d5faca1a_akeneo.jpg/download"
                }
            }
        }, {
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
    "categories": ["categoryA1", "categoryA2"],
    "enabled": true,
    "values": {
        "a_scopable_price": [{
            "locale": null,
            "scope": "tablet",
            "data": [{
                "amount": "78.77",
                "currency": "CNY"
            }, {
                "amount": "10.50",
                "currency": "EUR"
            }, {
                "amount": "11.50",
                "currency": "USD"
            }]
        }, {
            "locale": null,
            "scope": "ecommerce",
            "data": [{
                "amount": "78.77",
                "currency": "CNY"
            }, {
                "amount": "10.50",
                "currency": "EUR"
            }, {
                "amount": "11.50",
                "currency": "USD"
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
        }, {
            "locale": "zh_CN",
            "scope": "ecommerce_china",
            "data": "China description"
        }
        ]
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
