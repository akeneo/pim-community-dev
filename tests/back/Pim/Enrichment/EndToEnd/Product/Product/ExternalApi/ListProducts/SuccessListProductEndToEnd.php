<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi\ListProducts;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Test\Integration\Configuration;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi\AbstractProductTestCase;
use Doctrine\Common\Collections\Collection;

/**
 * @group ce
 */
class SuccessListProductEndToEnd extends AbstractProductTestCase
{
    /** @var Collection */
    private $products;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

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
                    ['data' => $this->getFileInfoKey($this->getFixturePath('akeneo.jpg')), 'locale' => 'en_US', 'scope' => null],
                    ['data' => $this->getFileInfoKey($this->getFixturePath('akeneo.jpg')), 'locale' => 'fr_FR', 'scope' => null],
                    ['data' => $this->getFileInfoKey($this->getFixturePath('akeneo.jpg')), 'locale' => 'zh_CN', 'scope' => null]
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
        $this->createProduct('localizable_and_scopable', [
            'categories' => ['categoryA', 'master_china'],
            'values'     => [
                'a_localized_and_scopable_text_area' => [
                    ['data' => 'Big description', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                    ['data' => 'Medium description', 'locale' => 'en_US', 'scope' => 'tablet'],
                    ['data' => 'Great description', 'locale' => 'en_US', 'scope' => 'ecommerce_china'],
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

        $this->createProductModel(
            [
                'code' => 'parent_prod_mod',
                'family_variant' => 'familyVariantA1',
                'values'  => [
                    'a_price'  => [
                        'data' => ['data' => [['amount' => '50', 'currency' => 'EUR']], 'locale' => null, 'scope' => null],
                    ],
                    'a_number_float'  => [['data' => '12.5', 'locale' => null, 'scope' => null]],
                    'a_localized_and_scopable_text_area'  => [['data' => 'my pink tshirt', 'locale' => 'en_US', 'scope' => 'ecommerce']],
                ]
            ]
        );

        $this->createProductModel(
            [
                'code' => 'prod_mod_optA',
                'parent' => 'parent_prod_mod',
                'family_variant' => 'familyVariantA1',
                'values'  => [
                    'a_simple_select' => [
                        ['locale' => null, 'scope' => null, 'data' => 'optionA'],
                    ]
                ]
            ]
        );

        $this->createVariantProduct('product_with_parent', [
            'categories' => ['master'],
            'parent' => 'prod_mod_optA',
            'values' => [
                'a_yes_no' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => true,
                    ]
                ]
            ]
        ]);

        $this->products = $this->get('pim_catalog.repository.product')->findAll();
    }

    public function testDefaultPaginationFirstPageListProductsWithCount()
    {
        $standardizedProducts = $this->getStandardizedProducts();
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products?with_count=true&limit=3');
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href": "http://localhost/api/rest/v1/products?page=1&with_count=true&pagination_type=page&limit=3"},
        "first" : {"href": "http://localhost/api/rest/v1/products?page=1&with_count=true&pagination_type=page&limit=3"},
        "next"  : {"href": "http://localhost/api/rest/v1/products?page=2&with_count=true&pagination_type=page&limit=3"}
    },
    "current_page" : 1,
    "items_count"  : 7,
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

    public function testOffsetPaginationListProductsWithChannelLocalesAndAttributesParams()
    {
        $standardizedProducts = $this->getStandardizedProducts();
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products?scope=tablet&locales=fr_FR&attributes=a_scopable_price,a_metric,a_localized_and_scopable_text_area&pagination_type=page');
        $expected = <<<JSON
{
    "_links"       : {
        "self"  : {"href" : "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=10&scope=tablet&locales=fr_FR&attributes=a_scopable_price,a_metric,a_localized_and_scopable_text_area"},
        "first" : {"href" : "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=10&scope=tablet&locales=fr_FR&attributes=a_scopable_price,a_metric,a_localized_and_scopable_text_area"}
    },
    "current_page" : 1,
    "_embedded"    : {
        "items" : [
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/products/simple"}
                },
                "identifier"    : "simple",
                "family"        : null,
                "parent"        : null,
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
                "associations"  : {
                    "PACK": { "products" : [], "product_models": [], "groups": [] },
                    "SUBSTITUTION": { "products" : [], "product_models": [], "groups": [] },
                    "UPSELL": { "products" : [], "product_models": [], "groups": [] },
                    "X_SELL": { "products" : [], "product_models": [], "groups": [] }
                }
            },
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/products/localizable"}
                },
                "identifier"    : "localizable",
                "family"        : null,
                "parent"        : null,
                "groups"        : [],
                "categories"    : ["categoryB"],
                "enabled"       : true,
                "values"        : [],
                "created"       : "2017-01-23T11:44:25+01:00",
                "updated"       : "2017-01-23T11:44:25+01:00",
                "associations"  : {
                    "PACK": { "products" : [], "product_models": [], "groups": [] },
                    "SUBSTITUTION": { "products" : [], "product_models": [], "groups": [] },
                    "UPSELL": { "products" : [], "product_models": [], "groups": [] },
                    "X_SELL": { "products" : [], "product_models": [], "groups": [] }
                }
            },
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/products/scopable"}
                },
                "identifier"    : "scopable",
                "family"        : null,
                "parent"        : null,
                "groups"        : [],
                "categories"    : ["categoryA1", "categoryA2"],
                "enabled"       : true,
                "values"        : {
                    "a_scopable_price" : [
                        {
                            "locale" : null,
                            "scope"  : "tablet",
                            "data"   : [
                                {"amount" : "10.50", "currency" : "EUR"},
                                {"amount" : "11.50", "currency" : "USD"}
                            ]
                        }
                    ]
                },
                "created"       : "2017-01-23T11:44:25+01:00",
                "updated"       : "2017-01-23T11:44:25+01:00",
                "associations"  : {
                    "PACK": { "products" : [], "product_models": [], "groups": [] },
                    "SUBSTITUTION": { "products" : [], "product_models": [], "groups": [] },
                    "UPSELL": { "products" : [], "product_models": [], "groups": [] },
                    "X_SELL": { "products" : [], "product_models": [], "groups": [] }
                }
            },
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/products/localizable_and_scopable"}
                },
                "identifier"    : "localizable_and_scopable",
                "family"        : null,
                "parent"        : null,
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
                "associations"  : {
                    "PACK": { "products" : [], "product_models": [], "groups": [] },
                    "SUBSTITUTION": { "products" : [], "product_models": [], "groups": [] },
                    "UPSELL": { "products" : [], "product_models": [], "groups": [] },
                    "X_SELL": { "products" : [], "product_models": [], "groups": [] }
                }
            },
            {
                "_links": {
                    "self": { "href": "http:\/\/localhost\/api\/rest\/v1\/products\/product_with_parent" }
                },
                "identifier": "product_with_parent",
                "enabled": true,
                "family": "familyA",
                "categories": ["master"],
                "groups": [],
                "parent": "prod_mod_optA",
                "values": { },
                "created": "2019-06-10T12:37:47+02:00",
                "updated": "2019-06-10T12:37:47+02:00",
                "associations": {
                    "PACK": { "products": [], "product_models": [], "groups": [] },
                    "UPSELL": { "products": [], "product_models": [], "groups": [] },
                    "X_SELL": { "products": [], "product_models": [], "groups": [] },
                    "SUBSTITUTION": { "products": [], "product_models": [], "groups": [] }
                }
            }
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testOffsetPaginationListProductsWithSearch()
    {
        $client = $this->createAuthenticatedClient();

        $search = '{"a_metric":[{"operator":">","value":{"amount":"9","unit":"KILOWATT"}}],"enabled":[{"operator":"=","value":true}]}';
        $client->request('GET', 'api/rest/v1/products?pagination_type=page&search=' . $search);
        $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);
        $expected = <<<JSON
{
    "_links"       : {
        "self"  : {"href" : "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"},
        "first" : {"href" : "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"}
    },
    "current_page" : 1,
    "_embedded"    : {
        "items" : [
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/products/simple"}
                },
                "identifier"    : "simple",
                "family"        : null,
                "parent"        : null,
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
                "associations"  : {
                    "PACK": { "products" : [], "product_models": [], "groups": [] },
                    "SUBSTITUTION": { "products" : [], "product_models": [], "groups": [] },
                    "UPSELL": { "products" : [], "product_models": [], "groups": [] },
                    "X_SELL": { "products" : [], "product_models": [], "groups": [] }
                }
            }
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testListProductsWithSearchOnDateAttributesWithPositiveTimeZoneOffset()
    {
        $standardizedProducts = $this->getStandardizedProducts();
        $client = $this->createAuthenticatedClient();

        date_default_timezone_set('Pacific/Kiritimati');

        $currentDate = (new \DateTime('now'))->modify("- 30 minutes")->format('Y-m-d H:i:s');

        $search = sprintf('{"updated":[{"operator":">","value":"%s"}]}', $currentDate);
        $client->request('GET', 'api/rest/v1/products?pagination_type=page&limit=10&search=' . $search);
        $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);
        $expected = <<<JSON
{
    "_links"       : {
        "self"  : {"href" : "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"},
        "first" : {"href" : "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"}
    },
    "current_page" : 1,
    "_embedded"    : {
        "items" : [            
            {$standardizedProducts['simple']},
            {$standardizedProducts['localizable']},
            {$standardizedProducts['scopable']},
            {$standardizedProducts['localizable_and_scopable']},
            {$standardizedProducts['product_china']},
            {$standardizedProducts['product_without_category']},
            {$standardizedProducts['product_with_parent']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testListProductsWithBetweenOnDateAttributesWithPositiveTimeZoneOffset()
    {
        $standardizedProducts = $this->getStandardizedProducts();
        $client = $this->createAuthenticatedClient();

        date_default_timezone_set('Pacific/Kiritimati');

        $currentDate = (new \DateTime('now'))->format('Y-m-d H:i:s');
        $currentDateMinusHalf = (new \DateTime('now'))->modify('- 30 minutes')->format('Y-m-d H:i:s');

        $search = sprintf('{"updated":[{"operator":"BETWEEN","value":["%s","%s"]}]}', $currentDateMinusHalf, $currentDate );
        $client->request('GET', 'api/rest/v1/products?pagination_type=page&limit=10&search=' . $search);
        $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);
        $expected = <<<JSON
{
    "_links"       : {
        "self"  : {"href" : "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"},
        "first" : {"href" : "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"}
    },
    "current_page" : 1,
    "_embedded"    : {
        "items" : [            
            {$standardizedProducts['simple']},
            {$standardizedProducts['localizable']},
            {$standardizedProducts['scopable']},
            {$standardizedProducts['localizable_and_scopable']},
            {$standardizedProducts['product_china']},
            {$standardizedProducts['product_without_category']},
            {$standardizedProducts['product_with_parent']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testListProductsWithSearchOnDateAttributesWithNegativeTimeZoneOffset()
    {
        $standardizedProducts = $this->getStandardizedProducts();
        $client = $this->createAuthenticatedClient();

        date_default_timezone_set('America/Los_Angeles');

        $currentDate = (new \DateTime('now'))->modify("+ 30 minutes")->format('Y-m-d H:i:s');

        $search = sprintf('{"updated":[{"operator":"<","value":"%s"}]}', $currentDate);
        $client->request('GET', 'api/rest/v1/products?pagination_type=page&limit=10&search=' . $search);
        $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);
        $expected = <<<JSON
{
    "_links"       : {
        "self"  : {"href" : "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"},
        "first" : {"href" : "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"}
    },
    "current_page" : 1,
    "_embedded"    : {
        "items" : [            
            {$standardizedProducts['simple']},
            {$standardizedProducts['localizable']},
            {$standardizedProducts['scopable']},
            {$standardizedProducts['localizable_and_scopable']},
            {$standardizedProducts['product_china']},
            {$standardizedProducts['product_without_category']},
            {$standardizedProducts['product_with_parent']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testListProductsUpdatedSinceLastNDays()
    {
        $standardizedProducts = $this->getStandardizedProducts();
        $client = $this->createAuthenticatedClient();

        $search = sprintf('{"updated":[{"operator":"%s","value":4}]}', Operators::SINCE_LAST_N_DAYS);
        $client->request('GET', 'api/rest/v1/products?pagination_type=page&limit=10&search=' . $search);
        $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);
        $expected = <<<JSON
{
    "_links"       : {
        "self"  : {"href" : "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"},
        "first" : {"href" : "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"}
    },
    "current_page" : 1,
    "_embedded"    : {
        "items" : [            
            {$standardizedProducts['simple']},
            {$standardizedProducts['localizable']},
            {$standardizedProducts['scopable']},
            {$standardizedProducts['localizable_and_scopable']},
            {$standardizedProducts['product_china']},
            {$standardizedProducts['product_without_category']},
            {$standardizedProducts['product_with_parent']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testOffsetPaginationListProductsWithMultiplePQBFilters()
    {
        $client = $this->createAuthenticatedClient();

        $search = '{"categories":[{"operator":"IN","value":["categoryB"]}],"a_yes_no":[{"operator":"=","value":true}]}';
        $client->request('GET', 'api/rest/v1/products?pagination_type=page&search=' . $search);
        $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href" : "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"},
        "first" : {"href" : "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"}
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

        $search = '{"completeness":[{"operator":"GREATER THAN ON ALL LOCALES","value":50,"locales":["fr_FR"],"scope":"ecommerce"}],"categories":[{"operator":"IN","value":["categoryB"]}],"a_yes_no":[{"operator":"=","value":true}]}';
        $client->request('GET', 'api/rest/v1/products?search=' . $search);
        $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href" : "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"},
        "first" : {"href" : "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"}
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
     *
     * @group critical
     */
    public function testSearchAfterPaginationListProductsWithoutParameter()
    {
        $standardizedProducts = $this->getStandardizedProducts();
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products?pagination_type=search_after');
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href": "http://localhost/api/rest/v1/products?with_count=false&pagination_type=search_after&limit=10"},
        "first" : {"href": "http://localhost/api/rest/v1/products?with_count=false&pagination_type=search_after&limit=10"}
    },
    "_embedded" : {
        "items" : [
            {$standardizedProducts['simple']},
            {$standardizedProducts['localizable']},
            {$standardizedProducts['scopable']},
            {$standardizedProducts['localizable_and_scopable']},
            {$standardizedProducts['product_china']},
            {$standardizedProducts['product_without_category']},
            {$standardizedProducts['product_with_parent']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    /**
     * Get all products, whatever locale, scope, category with a search after pagination
     *
     * @group critical
     */
    public function testSearchAfterPaginationListProductsWithNextLink()
    {
        $standardizedProducts = $this->getStandardizedProducts();
        $client = $this->createAuthenticatedClient();

        $id = [
            'simple'                   => $this->encodeStringWithSymfonyUrlGeneratorCompatibility($this->getEncryptedId('simple')),
            'localizable'              => $this->encodeStringWithSymfonyUrlGeneratorCompatibility($this->getEncryptedId('localizable')),
            'localizable_and_scopable' => $this->encodeStringWithSymfonyUrlGeneratorCompatibility($this->getEncryptedId('localizable_and_scopable')),
        ];

        $client->request('GET', sprintf('api/rest/v1/products?pagination_type=search_after&limit=3&search_after=%s', $id['simple']));
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href": "http://localhost/api/rest/v1/products?with_count=false&pagination_type=search_after&limit=3&search_after={$id['simple']}"},
        "first" : {"href": "http://localhost/api/rest/v1/products?with_count=false&pagination_type=search_after&limit=3"},
        "next"  : {"href": "http://localhost/api/rest/v1/products?with_count=false&pagination_type=search_after&limit=3&search_after={$id['localizable_and_scopable']}"}
    },
    "_embedded"    : {
        "items" : [
            {$standardizedProducts['localizable']},
            {$standardizedProducts['scopable']},
            {$standardizedProducts['localizable_and_scopable']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testSearchAfterPaginationLastPageOfTheListOfProducts()
    {
        $standardizedProducts = $this->getStandardizedProducts();
        $client = $this->createAuthenticatedClient();

        $scopableEncryptedId = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($this->getEncryptedId('scopable'));

        $client->request('GET', sprintf('api/rest/v1/products?pagination_type=search_after&limit=5&search_after=%s' , $scopableEncryptedId));
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href": "http://localhost/api/rest/v1/products?with_count=false&pagination_type=search_after&limit=5&search_after={$scopableEncryptedId}"},
        "first" : {"href": "http://localhost/api/rest/v1/products?with_count=false&pagination_type=search_after&limit=5"}
    },
    "_embedded"    : {
        "items" : [
            {$standardizedProducts['localizable_and_scopable']},
            {$standardizedProducts['product_china']},
            {$standardizedProducts['product_without_category']},
            {$standardizedProducts['product_with_parent']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testListProductsWithParent()
    {
        $standardizedProducts = $this->getStandardizedProducts();
        $client = $this->createAuthenticatedClient();

        $search = '{"parent":[{"operator":"=","value":"prod_mod_optA"}]}';
        $client->request('GET', 'api/rest/v1/products?search=' . $search);
        $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href" : "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"},
        "first" : {"href" : "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"}
    },
    "current_page" : 1,
    "_embedded"    : {
        "items" : [
            {$standardizedProducts['product_with_parent']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    /**
     * @param string $productIdentifier
     *
     * @return string
     */
    private function getEncryptedId(string $productIdentifier): string
    {
        $encrypter = $this->get('pim_api.security.primary_key_encrypter');
        $productRepository = $this->get('pim_catalog.repository.product');

        $product = $productRepository->findOneByIdentifier($productIdentifier);

        return $encrypter->encrypt($product->getId());
    }

    /**
     * @return array
     */
    private function getStandardizedProducts(): array
    {
        $standardizedProducts['simple'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/products/simple"
        }
    },
    "identifier": "simple",
    "family": null,
    "parent": null,
    "groups": [],
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
    "associations": {
        "PACK": { "products" : [], "product_models": [], "groups": [] },
        "SUBSTITUTION": { "products" : [], "product_models": [], "groups": [] },
        "UPSELL": { "products" : [], "product_models": [], "groups": [] },
        "X_SELL": { "products" : [], "product_models": [], "groups": [] }
    }
}
JSON;

        $standardizedProducts['localizable'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/products/localizable"
        }
    },
    "identifier": "localizable",
    "family": null,
    "parent": null,
    "groups": [],
    "categories": ["categoryB"],
    "enabled": true,
    "values": {
        "a_localizable_image": [{
            "locale": "en_US",
            "scope": null,
            "data": "c/2/0/f/c20f1a4b3e6515d5676e89d52fb9e25fa1d29bd8_akeneo.jpg",
            "_links": {
                "download": {
                    "href": "http://localhost/api/rest/v1/media-files/c/2/0/f/c20f1a4b3e6515d5676e89d52fb9e25fa1d29bd8_akeneo.jpg/download"
                }
            }
        }, {
            "locale": "fr_FR",
            "scope": null,
            "data": "5/3/9/a/539a12626cb2fbc62cf7ad5f817174cce02b0519_akeneo.jpg",
            "_links": {
                "download": {
                    "href": "http://localhost/api/rest/v1/media-files/5/3/9/a/539a12626cb2fbc62cf7ad5f817174cce02b0519_akeneo.jpg/download"
                }
            }
        }, {
            "locale": "zh_CN",
            "scope": null,
            "data": "5/d/c/a/5dcac0871503e513d5be25807794a09ad9080341_akeneo.jpg",
            "_links": {
                "download": {
                    "href": "http://localhost/api/rest/v1/media-files/5/d/c/a/5dcac0871503e513d5be25807794a09ad9080341_akeneo.jpg/download"
                }
            }
        }]
    },
    "created": "2017-03-11T10:39:38+01:00",
    "updated": "2017-03-11T10:39:38+01:00",
    "associations": {
        "PACK": { "products" : [], "product_models": [], "groups": [] },
        "SUBSTITUTION": { "products" : [], "product_models": [], "groups": [] },
        "UPSELL": { "products" : [], "product_models": [], "groups": [] },
        "X_SELL": { "products" : [], "product_models": [], "groups": [] }
    }
}
JSON;

        $standardizedProducts['scopable'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/products/scopable"
        }
    },
    "identifier": "scopable",
    "family": null,
    "parent": null,
    "groups": [],
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
            }]
        }]
    },
    "created": "2017-03-11T10:39:38+01:00",
    "updated": "2017-03-11T10:39:38+01:00",
    "associations": {
        "PACK": { "products" : [], "product_models": [], "groups": [] },
        "SUBSTITUTION": { "products" : [], "product_models": [], "groups": [] },
        "UPSELL": { "products" : [], "product_models": [], "groups": [] },
        "X_SELL": { "products" : [], "product_models": [], "groups": [] }
    }
}
JSON;

        $standardizedProducts['localizable_and_scopable'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/products/localizable_and_scopable"
        }
    },
    "identifier": "localizable_and_scopable",
    "family": null,
    "parent": null,
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
            "locale": "en_US",
            "scope": "ecommerce_china",
            "data": "Great description"
        }, {
            "locale": "zh_CN",
            "scope": "ecommerce_china",
            "data": "hum..."
        }]
    },
    "created": "2017-03-11T10:39:38+01:00",
    "updated": "2017-03-11T10:39:38+01:00",
    "associations": {
        "PACK": { "products" : [], "product_models": [], "groups": [] },
        "SUBSTITUTION": { "products" : [], "product_models": [], "groups": [] },
        "UPSELL": { "products" : [], "product_models": [], "groups": [] },
        "X_SELL": { "products" : [], "product_models": [], "groups": [] }
    }
}
JSON;

        $standardizedProducts['product_china'] = <<<JSON
{
   "_links": {
       "self": {
           "href": "http://localhost/api/rest/v1/products/product_china"
       }
   },
   "identifier": "product_china",
   "family": null,
   "parent": null,
   "groups": [],
   "categories": ["master_china"],
   "enabled": true,
   "values": {},
   "created": "2017-03-11T10:39:38+01:00",
   "updated": "2017-03-11T10:39:38+01:00",
   "associations": {
        "PACK": { "products" : [], "product_models": [], "groups": [] },
        "SUBSTITUTION": { "products" : [], "product_models": [], "groups": [] },
        "UPSELL": { "products" : [], "product_models": [], "groups": [] },
        "X_SELL": { "products" : [], "product_models": [], "groups": [] }
    }
}
JSON;

        $standardizedProducts['product_without_category'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/products/product_without_category"
        }
    },
    "identifier": "product_without_category",
    "family": null,
    "parent": null,
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
    "associations": {
        "PACK": { "products" : [], "product_models": [], "groups": [] },
        "SUBSTITUTION": { "products" : [], "product_models": [], "groups": [] },
        "UPSELL": { "products" : [], "product_models": [], "groups": [] },
        "X_SELL": { "products" : [], "product_models": [], "groups": [] }
    }
}
JSON;

        $standardizedProducts['product_with_parent'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http:\/\/localhost\/api\/rest\/v1\/products\/product_with_parent"
        }
	},
    "identifier": "product_with_parent",
    "enabled": true,
    "family": "familyA",
    "categories": ["master"],
    "groups": [],
    "parent": "prod_mod_optA",
    "values": {
        "a_simple_select": [{ "locale": null, "scope": null, "data": "optionA" }],
        "a_price": [{ "locale": null, "scope": null, "data": [{ "amount": "50.00", "currency": "EUR" }] }],
        "a_yes_no": [{ "locale": null, "scope": null, "data": true }],
        "a_number_float": [{ "locale": null, "scope": null, "data": "12.5000" }],
        "a_localized_and_scopable_text_area": [{ "locale": "en_US", "scope": "ecommerce", "data": "my pink tshirt" }]
    },
    "created": "2019-06-10T12:37:47+02:00",
    "updated": "2019-06-10T12:37:47+02:00",
    "associations": {
        "PACK": { "products": [], "product_models": [], "groups": [] },
        "UPSELL": { "products": [], "product_models": [], "groups": [] },
        "X_SELL": { "products": [], "product_models": [], "groups": [] },
        "SUBSTITUTION": { "products": [], "product_models": [], "groups": [] }
    }
}
JSON;

        return $standardizedProducts;
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
