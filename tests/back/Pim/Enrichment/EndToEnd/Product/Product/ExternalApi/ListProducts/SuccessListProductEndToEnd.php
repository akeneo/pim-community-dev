<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi\ListProducts;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductUuidFactory;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Test\Integration\Configuration;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi\AbstractProductTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group ce
 */
class SuccessListProductEndToEnd extends AbstractProductTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        // no locale, no scope, 1 category
        $this->createProduct('simple', [
            'categories' => ['master'],
            'values' => [
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
            'values' => [
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
            'values' => [
                'a_scopable_price' => [
                    [
                        'locale' => null,
                        'scope' => 'ecommerce',
                        'data' => [
                            ['amount' => '78.77', 'currency' => 'CNY'],
                            ['amount' => '10.50', 'currency' => 'EUR'],
                            ['amount' => '11.50', 'currency' => 'USD'],
                        ]
                    ],
                    [
                        'locale' => null,
                        'scope' => 'tablet',
                        'data' => [
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
            'values' => [
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
                'values' => [
                    'a_price' => [
                        'data' => ['data' => [['amount' => '50', 'currency' => 'EUR']], 'locale' => null, 'scope' => null],
                    ],
                    'a_number_float' => [['data' => '12.5', 'locale' => null, 'scope' => null]],
                    'a_localized_and_scopable_text_area' => [['data' => 'my pink tshirt', 'locale' => 'en_US', 'scope' => 'ecommerce']],
                ]
            ]
        );

        $this->createProductModel(
            [
                'code' => 'prod_mod_optA',
                'parent' => 'parent_prod_mod',
                'family_variant' => 'familyVariantA1',
                'values' => [
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
            {$standardizedProducts['localizable']},
            {$standardizedProducts['localizable_and_scopable']},
            {$standardizedProducts['product_china']}
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
                },
                "quantified_associations": {}
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
                },
                "quantified_associations": {}
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
                },
                "quantified_associations": {}
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
                },
                "quantified_associations": {}
            },
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
                },
                "quantified_associations": {}
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
                },
                "quantified_associations": {}
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
            {$standardizedProducts['localizable']},
            {$standardizedProducts['localizable_and_scopable']},
            {$standardizedProducts['product_china']},
            {$standardizedProducts['product_with_parent']},
            {$standardizedProducts['product_without_category']},
            {$standardizedProducts['scopable']},
            {$standardizedProducts['simple']}
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

        $search = sprintf('{"updated":[{"operator":"BETWEEN","value":["%s","%s"]}]}', $currentDateMinusHalf, $currentDate);
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
            {$standardizedProducts['localizable']},
            {$standardizedProducts['localizable_and_scopable']},
            {$standardizedProducts['product_china']},
            {$standardizedProducts['product_with_parent']},
            {$standardizedProducts['product_without_category']},
            {$standardizedProducts['scopable']},
            {$standardizedProducts['simple']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testListProductsWithAttributeOptions()
    {
        $standardizedProducts = $this->getStandardizedProducts(true);
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/rest/v1/products?with_attribute_options=true');

        $expected = <<<JSON
{
    "_links"       : {
        "self"  : {"href" : "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=10&with_attribute_options=true"},
        "first" : {"href" : "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=10&with_attribute_options=true"}
    },
    "current_page" : 1,
    "_embedded"    : {
        "items" : [
            {$standardizedProducts['localizable']},
            {$standardizedProducts['localizable_and_scopable']},
            {$standardizedProducts['product_china']},
            {$standardizedProducts['product_with_parent']},
            {$standardizedProducts['product_without_category']},
            {$standardizedProducts['scopable']},
            {$standardizedProducts['simple']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testListProductsWithQualityScores()
    {
        $product1 = $this->createProduct('simple_with_family_and_values', [
            'categories' => ['master'],
            'family' => 'familyA',
            'values' => [
                'a_text' => [
                    ['data' => 'Text', 'locale' => null, 'scope' => null]
                ]
            ],
        ]);
        $product2 = $this->createProduct('simple_with_no_family', [
            'categories' => ['master'],
            'values' => [
                'a_text' => [
                    ['data' => 'Text', 'locale' => null, 'scope' => null]
                ]
            ],
        ]);
        $product3 = $this->createProduct('simple_with_no_values', [
            'categories' => ['master'],
            'family' => 'familyA',
            'values' => [],
        ]);

        ($this->get('Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProducts'))(
            $this->get(ProductUuidFactory::class)->createCollection([
                (string) $product1->getUuid(),
                (string) $product2->getUuid(),
                (string) $product3->getUuid(),
            ])
        );

        $values = '{
            "a_text": [{
                "locale": null,
                "scope": null,
                "data": "Text"
            }]
        }';
        $qualityScores = '[
            {"scope": "tablet", "locale": "de_DE", "data": "E"},
            {"scope": "tablet", "locale": "en_US", "data": "E"},
            {"scope": "tablet", "locale": "fr_FR", "data": "E"},
            {"scope": "ecommerce", "locale": "en_US", "data": "E"},
            {"scope": "ecommerce_china", "locale": "en_US", "data": "E"},
            {"scope": "ecommerce_china", "locale": "zh_CN", "data": "E"}
        ]';
        $standardizedProducts['simple_with_family_and_values'] = $this->getStandardizedProductsForQualityScore('simple_with_family_and_values', '"familyA"', $values, $qualityScores);
        $standardizedProducts['simple_with_no_family'] = $this->getStandardizedProductsForQualityScore('simple_with_no_family', 'null', $values, '[]');
        $standardizedProducts['simple_with_no_values'] = $this->getStandardizedProductsForQualityScore('simple_with_no_values', '"familyA"', '{}', $qualityScores);

        $client = $this->createAuthenticatedClient();
        $search = '{"sku":[{"operator":"IN","value":["simple_with_family_and_values","simple_with_no_family","simple_with_no_values"]}]}';
        $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);
        $client->request('GET', "/api/rest/v1/products?with_quality_scores=true&search=$searchEncoded");

        $expected = <<<JSON
{
    "_links"       : {
        "self"  : {"href" : "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=10&search=$searchEncoded&with_quality_scores=true"},
        "first" : {"href" : "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=10&search=$searchEncoded&with_quality_scores=true"}
    },
    "current_page" : 1,
    "_embedded"    : {
        "items" : [
            {$standardizedProducts['simple_with_family_and_values']},
            {$standardizedProducts['simple_with_no_family']},
            {$standardizedProducts['simple_with_no_values']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testListProductsWithCompletenesses()
    {
        $this->createProduct('simple_with_family_and_values', [
            'categories' => ['master'],
            'family' => 'familyA',
            'values' => [
                'a_text' => [
                    ['data' => 'Text', 'locale' => null, 'scope' => null]
                ]
            ],
        ]);
        $this->createProduct('simple_with_no_family', [
            'categories' => ['master'],
            'values' => [
                'a_text' => [
                    ['data' => 'Text', 'locale' => null, 'scope' => null]
                ]
            ],
        ]);
        $this->createProduct('simple_with_no_values', [
            'categories' => ['master'],
            'family' => 'familyA',
            'values' => [],
        ]);

        $values = '{
            "a_text": [{
                "locale": null,
                "scope": null,
                "data": "Text"
            }]
        }';
        $completenessesFamVal = '[
            {"scope":"ecommerce","locale":"en_US","data":10},
            {"scope":"ecommerce_china","locale":"en_US","data":100},
            {"scope":"ecommerce_china","locale":"zh_CN","data":100},
            {"scope":"tablet","locale":"de_DE","data":10},
            {"scope":"tablet","locale":"en_US","data":10},
            {"scope":"tablet","locale":"fr_FR","data":10}
        ]';
        $completenessesNoVal = '[
            {"scope":"ecommerce","locale":"en_US","data":5},
            {"scope":"ecommerce_china","locale":"en_US","data":100},
            {"scope":"ecommerce_china","locale":"zh_CN","data":100},
            {"scope":"tablet","locale":"de_DE","data":5},
            {"scope":"tablet","locale":"en_US","data":5},
            {"scope":"tablet","locale":"fr_FR","data":5}
        ]';
        $standardizedProducts['simple_with_family_and_values'] = $this->getStandardizedProductsForCompletenesses('simple_with_family_and_values', '"familyA"', $values, $completenessesFamVal);
        $standardizedProducts['simple_with_no_family'] = $this->getStandardizedProductsForCompletenesses('simple_with_no_family', 'null', $values, '[]');
        $standardizedProducts['simple_with_no_values'] = $this->getStandardizedProductsForCompletenesses('simple_with_no_values', '"familyA"', '{}', $completenessesNoVal);

        $client = $this->createAuthenticatedClient();
        $search = '{"sku":[{"operator":"IN","value":["simple_with_family_and_values","simple_with_no_family","simple_with_no_values"]}]}';
        $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);
        $client->request('GET', "/api/rest/v1/products?with_completenesses=true&search=$searchEncoded");

        $expected = <<<JSON
{
    "_links"       : {
        "self"  : {"href" : "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=10&search=$searchEncoded&with_completenesses=true"},
        "first" : {"href" : "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=10&search=$searchEncoded&with_completenesses=true"}
    },
    "current_page" : 1,
    "_embedded"    : {
        "items" : [
            {$standardizedProducts['simple_with_family_and_values']},
            {$standardizedProducts['simple_with_no_family']},
            {$standardizedProducts['simple_with_no_values']}
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
            {$standardizedProducts['localizable']},
            {$standardizedProducts['localizable_and_scopable']},
            {$standardizedProducts['product_china']},
            {$standardizedProducts['product_with_parent']},
            {$standardizedProducts['product_without_category']},
            {$standardizedProducts['scopable']},
            {$standardizedProducts['simple']}
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
            {$standardizedProducts['localizable']},
            {$standardizedProducts['localizable_and_scopable']},
            {$standardizedProducts['product_china']},
            {$standardizedProducts['product_with_parent']},
            {$standardizedProducts['product_without_category']},
            {$standardizedProducts['scopable']},
            {$standardizedProducts['simple']}
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
            {$standardizedProducts['localizable']},
            {$standardizedProducts['localizable_and_scopable']},
            {$standardizedProducts['product_china']},
            {$standardizedProducts['product_with_parent']},
            {$standardizedProducts['product_without_category']},
            {$standardizedProducts['scopable']},
            {$standardizedProducts['simple']}
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

        $client->request('GET', sprintf('api/rest/v1/products?pagination_type=search_after&limit=3&search_after=%s', 'product_china'));
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href": "http://localhost/api/rest/v1/products?with_count=false&pagination_type=search_after&limit=3&search_after=product_china"},
        "first" : {"href": "http://localhost/api/rest/v1/products?with_count=false&pagination_type=search_after&limit=3"},
        "next"  : {"href": "http://localhost/api/rest/v1/products?with_count=false&pagination_type=search_after&limit=3&search_after=scopable"}
    },
    "_embedded"    : {
        "items" : [
            {$standardizedProducts['product_with_parent']},
            {$standardizedProducts['product_without_category']},
            {$standardizedProducts['scopable']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testSearchAfterPaginationWithUppercaseIdentifier(): void
    {
        $this->createProduct('AN_UPPERCASE_IDENTIFIER', []);
        $this->createProduct('MY_OTHER_UPPERCASE_IDENTIFIER', []);

        $standardizedProducts = $this->getStandardizedProducts();
        $client = $this->createAuthenticatedClient();

        $client->request(
            'GET',
            sprintf('api/rest/v1/products?pagination_type=search_after&limit=3&search_after=%s', 'AN_UPPERCASE_IDENTIFIER')
        );
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href": "http://localhost/api/rest/v1/products?with_count=false&pagination_type=search_after&limit=3&search_after=AN_UPPERCASE_IDENTIFIER"},
        "first" : {"href": "http://localhost/api/rest/v1/products?with_count=false&pagination_type=search_after&limit=3"},
        "next"  : {"href": "http://localhost/api/rest/v1/products?with_count=false&pagination_type=search_after&limit=3&search_after=MY_OTHER_UPPERCASE_IDENTIFIER"}
    },
    "_embedded"    : {
        "items" : [
            {$standardizedProducts['localizable']},
            {$standardizedProducts['localizable_and_scopable']},
            {
                "_links": {
                    "self": {
                        "href": "http://localhost/api/rest/v1/products/MY_OTHER_UPPERCASE_IDENTIFIER"
                    }
                },
                "identifier": "MY_OTHER_UPPERCASE_IDENTIFIER",
                "family": null,
                "parent": null,
                "groups": [],
                "categories": [],
                "enabled": true,
                "values": {},
                "created": "2017-03-11T10:39:38+01:00",
                "updated": "2017-03-11T10:39:38+01:00",
                "associations": {
                    "PACK": { "products" : [], "product_models": [], "groups": [] },
                    "SUBSTITUTION": { "products" : [], "product_models": [], "groups": [] },
                    "UPSELL": { "products" : [], "product_models": [], "groups": [] },
                    "X_SELL": { "products" : [], "product_models": [], "groups": [] }
                },
                "quantified_associations": {}
            }
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

        $client->request('GET', sprintf('api/rest/v1/products?pagination_type=search_after&limit=5&search_after=%s', 'product_china'));
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href": "http://localhost/api/rest/v1/products?with_count=false&pagination_type=search_after&limit=5&search_after=product_china"},
        "first" : {"href": "http://localhost/api/rest/v1/products?with_count=false&pagination_type=search_after&limit=5"}
    },
    "_embedded"    : {
        "items" : [
            {$standardizedProducts['product_with_parent']},
            {$standardizedProducts['product_without_category']},
            {$standardizedProducts['scopable']},
            {$standardizedProducts['simple']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testListProductsWithParent()
    {
        $standardizedProducts = $this->getStandardizedProducts();

        $searchFilters = [
            '{"parent":[{"operator":"=","value":"prod_mod_optA"}]}',
            '{"parent":[{"operator":"NOT EMPTY","value":null}]}',
            '{"parent":[{"operator":"IN","value":["prod_mod_optA"]}]}',
        ];

        foreach ($searchFilters as $search) {
            $client = $this->createAuthenticatedClient();
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
    }

    public function testListProductsWithoutParent()
    {
        $standardizedProducts = $this->getStandardizedProducts();
        $client = $this->createAuthenticatedClient();

        $search = '{"parent":[{"operator":"EMPTY","value":null}]}';
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
            {$standardizedProducts['localizable']},
            {$standardizedProducts['localizable_and_scopable']},
            {$standardizedProducts['product_china']},
            {$standardizedProducts['product_without_category']},
            {$standardizedProducts['scopable']},
            {$standardizedProducts['simple']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testAccessDeniedWhenRetrievingProductsWithoutTheAcl()
    {
        $client = $this->createAuthenticatedClient();
        $this->removeAclFromRole('action:pim_api_product_list');

        $client->request('GET', 'api/rest/v1/products');
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    /**
     * @return array
     */
    private function getStandardizedProducts(bool $withAttributeOptions = false): array
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
    },
    "quantified_associations": {}
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
    },
    "quantified_associations": {}
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
    },
    "quantified_associations": {}
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
    },
    "quantified_associations": {}
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
   },
   "quantified_associations": {}
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
    },
    "quantified_associations": {}
}
JSON;

        if ($withAttributeOptions) {
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
        "a_simple_select": [
            {
                "locale": null,
                "scope": null,
                "data": "optionA",
                "linked_data": {
                    "attribute": "a_simple_select",
                     "code": "optionA",
                     "labels": {
                        "en_US": "Option A"
                     }
                }
            }
        ],
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
    },
    "quantified_associations": {}
}
JSON;

        } else {
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
    },
    "quantified_associations": {}
}
JSON;
        }

        return $standardizedProducts;
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getStandardizedProductsForQualityScore(string $identifier, string $family, string $values, string $qualityScores)
    {
        return <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/products/$identifier"
        }
    },
    "identifier": "$identifier",
    "family": $family,
    "parent": null,
    "groups": [],
    "categories": ["master"],
    "enabled": true,
    "values": $values,
    "created": "2017-03-11T10:39:38+01:00",
    "updated": "2017-03-11T10:39:38+01:00",
    "associations": {
        "PACK": { "products" : [], "product_models": [], "groups": [] },
        "SUBSTITUTION": { "products" : [], "product_models": [], "groups": [] },
        "UPSELL": { "products" : [], "product_models": [], "groups": [] },
        "X_SELL": { "products" : [], "product_models": [], "groups": [] }
    },
    "quantified_associations": {},
    "quality_scores": $qualityScores
}
JSON;
    }

    private function getStandardizedProductsForCompletenesses(string $identifier, string $family, string $values, string $completenesses)
    {
        return <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/products/$identifier"
        }
    },
    "identifier": "$identifier",
    "family": $family,
    "parent": null,
    "groups": [],
    "categories": ["master"],
    "enabled": true,
    "values": $values,
    "created": "2017-03-11T10:39:38+01:00",
    "updated": "2017-03-11T10:39:38+01:00",
    "associations": {
        "PACK": { "products" : [], "product_models": [], "groups": [] },
        "SUBSTITUTION": { "products" : [], "product_models": [], "groups": [] },
        "UPSELL": { "products" : [], "product_models": [], "groups": [] },
        "X_SELL": { "products" : [], "product_models": [], "groups": [] }
    },
    "quantified_associations": {},
    "completenesses": $completenesses
}
JSON;
    }
}
