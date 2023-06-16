<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi\ListProducts;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductUuidFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\PriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetImageValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMeasurementValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetPriceCollectionValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Test\Integration\Configuration;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi\AbstractProductTestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group ce
 */
class SuccessListProductsWithUuidEndToEnd extends AbstractProductTestCase
{
    /** @var ProductInterface[] $products */
    private array $products = [];

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        // no locale, no scope, 1 category
        $this->products['simple'] = $this->createProduct('simple', [
            new SetCategories(['master']),
            new SetMeasurementValue('a_metric', null, null, 10, 'KILOWATT'),
            new SetTextValue('a_text', null, null, 'Text')
        ]);

        // localizable, categorized in 1 tree (master)
        $path = $this->getFileInfoKey($this->getFixturePath('akeneo.jpg'));
        $this->products['localizable'] = $this->createProduct('localizable', [
            new SetCategories(['categoryB']),
            new SetImageValue('a_localizable_image', null, 'en_US', $path),
            new SetImageValue('a_localizable_image', null, 'fr_FR', $path),
            new SetImageValue('a_localizable_image', null, 'zh_CN', $path),
        ]);

        // scopable, categorized in 1 tree (master)
        $this->products['scopable'] = $this->createProduct('scopable', [
            new SetCategories(['categoryA1', 'categoryA2']),
            new SetPriceCollectionValue('a_scopable_price', 'ecommerce', null, [
                new PriceValue('78.77', 'CNY'),
                new PriceValue('10.50', 'EUR'),
                new PriceValue('11.50', 'USD'),
            ]),
            new SetPriceCollectionValue('a_scopable_price', 'tablet', null, [
                new PriceValue('78.77', 'CNY'),
                new PriceValue('10.50', 'EUR'),
                new PriceValue('11.50', 'USD'),
            ]),
        ]);

        // localizable & scopable, categorized in 2 trees (master and master_china)
        $this->products['localizable_and_scopable'] = $this->createProduct('localizable_and_scopable', [
            new SetCategories(['categoryA', 'master_china']),
            new SetTextareaValue('a_localized_and_scopable_text_area', 'ecommerce', 'en_US', 'Big description'),
            new SetTextareaValue('a_localized_and_scopable_text_area', 'tablet', 'en_US', 'Medium description'),
            new SetTextareaValue('a_localized_and_scopable_text_area', 'ecommerce_china', 'en_US', 'Great description'),
            new SetTextareaValue('a_localized_and_scopable_text_area', 'tablet', 'fr_FR', 'Description moyenne'),
            new SetTextareaValue('a_localized_and_scopable_text_area', 'ecommerce_china', 'zh_CN', 'hum...'),
        ]);

        $this->products['china'] = $this->createProduct('china', [
            new SetCategories(['master_china'])
        ]);

        $this->products['without_category'] = $this->createProduct('without_category', [
            new SetBooleanValue('a_yes_no', null, null, true)
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

        $this->products['with_parent'] = $this->createVariantProduct('with_parent', [
            new SetCategories(['master']),
            new ChangeParent('prod_mod_optA'),
            new SetBooleanValue('a_yes_no', null, null, true)
        ]);
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset();
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }

    public function testDefaultPaginationFirstPageListProductsWithCount()
    {
        $standardizedProducts = $this->getStandardizedProducts();
        $client = $this->createAuthenticatedClient();

        $sortedProducts = \array_values($this->products);
        \usort($sortedProducts, fn (ProductInterface $p1, ProductInterface $p2): int => \strcmp($p1->getUuid()->toString(), $p2->getUuid()->toString()));

        $client->request('GET', 'api/rest/v1/products-uuid?with_count=true&limit=3');
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href": "http://localhost/api/rest/v1/products-uuid?page=1&with_count=true&pagination_type=page&limit=3"},
        "first" : {"href": "http://localhost/api/rest/v1/products-uuid?page=1&with_count=true&pagination_type=page&limit=3"},
        "next"  : {"href": "http://localhost/api/rest/v1/products-uuid?page=2&with_count=true&pagination_type=page&limit=3"}
    },
    "current_page" : 1,
    "items_count"  : 7,
    "_embedded"    : {
		"items": [
            {$standardizedProducts[$sortedProducts[0]->getIdentifier()]},
            {$standardizedProducts[$sortedProducts[1]->getIdentifier()]},
            {$standardizedProducts[$sortedProducts[2]->getIdentifier()]}
		]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected, true);
    }

    public function testOffsetPaginationListProductsWithChannelLocalesAndAttributesParams()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products-uuid?scope=tablet&locales=fr_FR&attributes=a_scopable_price,a_metric,a_localized_and_scopable_text_area&pagination_type=page');
        $expected = <<<JSON
{
    "_links"       : {
        "self"  : {"href" : "http://localhost/api/rest/v1/products-uuid?page=1&with_count=false&pagination_type=page&limit=10&scope=tablet&locales=fr_FR&attributes=a_scopable_price,a_metric,a_localized_and_scopable_text_area"},
        "first" : {"href" : "http://localhost/api/rest/v1/products-uuid?page=1&with_count=false&pagination_type=page&limit=10&scope=tablet&locales=fr_FR&attributes=a_scopable_price,a_metric,a_localized_and_scopable_text_area"}
    },
    "current_page" : 1,
    "_embedded"    : {
        "items" : [
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/products-uuid/{localizableUuid}"}
                },
                "uuid"          : "{localizableUuid}",
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
                    "self" : {"href" : "http://localhost/api/rest/v1/products-uuid/{localizableAndScopableUuid}"}
                },
                "uuid"          : "{localizableAndScopableUuid}",
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
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/products-uuid/{scopableUuid}"}
                },
                "uuid"          : "{scopableUuid}",
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
                    "self" : {"href" : "http://localhost/api/rest/v1/products-uuid/{simpleUuid}"}
                },
                "uuid"          : "{simpleUuid}",
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
            },
            {
                "_links": {
                    "self": { "href": "http:\/\/localhost\/api\/rest\/v1\/products-uuid\/{productWithParentUuid}" }
                },
                "uuid": "{productWithParentUuid}",
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
            }
        ]
    }
}
JSON;

        $expected = \strtr($expected, [
            '{localizableUuid}' => $this->products['localizable']->getUuid()->toString(),
            '{localizableAndScopableUuid}' => $this->products['localizable_and_scopable']->getUuid()->toString(),
            '{productWithParentUuid}' => $this->products['with_parent']->getUuid()->toString(),
            '{scopableUuid}' => $this->products['scopable']->getUuid()->toString(),
            '{simpleUuid}' => $this->products['simple']->getUuid()->toString(),
        ]);

        $this->assertListResponse($client->getResponse(), $expected, true);
    }

    public function testOffsetPaginationListProductsWithSearch()
    {
        $standardizedProducts = $this->getStandardizedProducts();
        $client = $this->createAuthenticatedClient();

        $search = '{"a_metric":[{"operator":">","value":{"amount":"9","unit":"KILOWATT"}}],"enabled":[{"operator":"=","value":true}]}';
        $client->request('GET', 'api/rest/v1/products-uuid?pagination_type=page&search=' . $search);
        $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);
        $expected = <<<JSON
{
    "_links"       : {
        "self"  : {"href" : "http://localhost/api/rest/v1/products-uuid?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"},
        "first" : {"href" : "http://localhost/api/rest/v1/products-uuid?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"}
    },
    "current_page" : 1,
    "_embedded"    : {
        "items" : [   
            {$standardizedProducts['simple']}
		]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected, true);
    }

    public function testListProductsWithSearchOnDateAttributesWithPositiveTimeZoneOffset()
    {
        $standardizedProducts = $this->getStandardizedProducts();
        $client = $this->createAuthenticatedClient();

        date_default_timezone_set('Pacific/Kiritimati');

        $currentDate = (new \DateTime('now'))->modify("- 30 minutes")->format('Y-m-d H:i:s');

        $search = sprintf('{"updated":[{"operator":">","value":"%s"}]}', $currentDate);
        $client->request('GET', 'api/rest/v1/products-uuid?pagination_type=page&limit=10&search=' . $search);
        $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);
        $expected = <<<JSON
{
    "_links"       : {
        "self"  : {"href" : "http://localhost/api/rest/v1/products-uuid?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"},
        "first" : {"href" : "http://localhost/api/rest/v1/products-uuid?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"}
    },
    "current_page" : 1,
    "_embedded"    : {
        "items" : [
            {$standardizedProducts['china']},
            {$standardizedProducts['localizable']},
            {$standardizedProducts['localizable_and_scopable']},
            {$standardizedProducts['scopable']},
            {$standardizedProducts['simple']},
            {$standardizedProducts['with_parent']},
            {$standardizedProducts['without_category']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected, true);
    }

    public function testListProductsWithBetweenOnDateAttributesWithPositiveTimeZoneOffset()
    {
        $standardizedProducts = $this->getStandardizedProducts();
        $client = $this->createAuthenticatedClient();

        date_default_timezone_set('Pacific/Kiritimati');

        $currentDate = (new \DateTime('now'))->format('Y-m-d H:i:s');
        $currentDateMinusHalf = (new \DateTime('now'))->modify('- 30 minutes')->format('Y-m-d H:i:s');

        $search = sprintf('{"updated":[{"operator":"BETWEEN","value":["%s","%s"]}]}', $currentDateMinusHalf, $currentDate);
        $client->request('GET', 'api/rest/v1/products-uuid?pagination_type=page&limit=10&search=' . $search);
        $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);
        $expected = <<<JSON
{
    "_links"       : {
        "self"  : {"href" : "http://localhost/api/rest/v1/products-uuid?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"},
        "first" : {"href" : "http://localhost/api/rest/v1/products-uuid?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"}
    },
    "current_page" : 1,
    "_embedded"    : {
        "items" : [
            {$standardizedProducts['china']},
            {$standardizedProducts['localizable']},
            {$standardizedProducts['localizable_and_scopable']},
            {$standardizedProducts['scopable']},
            {$standardizedProducts['simple']},
            {$standardizedProducts['with_parent']},
            {$standardizedProducts['without_category']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected, true);
    }

    public function testListProductsWithAttributeOptions()
    {
        $standardizedProducts = $this->getStandardizedProducts(true);
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/rest/v1/products-uuid?with_attribute_options=true');

        $expected = <<<JSON
{
    "_links"       : {
        "self"  : {"href" : "http://localhost/api/rest/v1/products-uuid?page=1&with_count=false&pagination_type=page&limit=10&with_attribute_options=true"},
        "first" : {"href" : "http://localhost/api/rest/v1/products-uuid?page=1&with_count=false&pagination_type=page&limit=10&with_attribute_options=true"}
    },
    "current_page" : 1,
    "_embedded"    : {
        "items" : [
            {$standardizedProducts['china']},
            {$standardizedProducts['localizable']},
            {$standardizedProducts['localizable_and_scopable']},
            {$standardizedProducts['scopable']},
            {$standardizedProducts['simple']},
            {$standardizedProducts['with_parent']},
            {$standardizedProducts['without_category']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected, true);
    }

    public function testListProductsWithQualityScores()
    {
        $product1 = $this->createProduct('simple_with_family_and_values', [
            new SetCategories(['master']),
            new SetFamily('familyA'),
            new SetTextValue('a_text', null, null, 'Text')
        ]);
        $product2 = $this->createProduct('simple_with_no_family', [
            new SetCategories(['master']),
            new SetTextValue('a_text', null, null, 'Text')
        ]);
        $product3 = $this->createProduct('simple_with_no_values', [
            new SetCategories(['master']),
            new SetFamily('familyA'),
        ]);

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        $this->get('Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProducts')->forPendingCriteria(
            $this->get(ProductUuidFactory::class)->createCollection([
                (string) $product1->getUuid(),
                (string) $product2->getUuid(),
                (string) $product3->getUuid(),
            ])
        );

        $valuesWithAText = '{
            "sku": [{
                "locale": null,
                "scope": null,
                "data": "{identifier}"
            }],
            "a_text": [{
                "locale": null,
                "scope": null,
                "data": "Text"
            }]
        }';

        $valuesWithoutAText = '{
            "sku": [{
                "locale": null,
                "scope": null,
                "data": "{identifier}"
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
        $standardizedProducts['simple_with_family_and_values'] = $this->getStandardizedProductsForQualityScore($product1, '"familyA"', $valuesWithAText, $qualityScores);
        $standardizedProducts['simple_with_no_family'] = $this->getStandardizedProductsForQualityScore($product2, 'null', $valuesWithAText, '[]');
        $standardizedProducts['simple_with_no_values'] = $this->getStandardizedProductsForQualityScore($product3, '"familyA"', $valuesWithoutAText, $qualityScores);

        $client = $this->createAuthenticatedClient();
        $search = '{"sku":[{"operator":"IN","value":["simple_with_family_and_values","simple_with_no_family","simple_with_no_values"]}]}';
        $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);
        $client->request('GET', "/api/rest/v1/products-uuid?with_quality_scores=true&search=$searchEncoded");

        $expected = <<<JSON
{
    "_links"       : {
        "self"  : {"href" : "http://localhost/api/rest/v1/products-uuid?page=1&with_count=false&pagination_type=page&limit=10&search=$searchEncoded&with_quality_scores=true"},
        "first" : {"href" : "http://localhost/api/rest/v1/products-uuid?page=1&with_count=false&pagination_type=page&limit=10&search=$searchEncoded&with_quality_scores=true"}
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

        $this->assertListResponse($client->getResponse(), $expected, true);
    }

    public function testListProductsWithCompletenesses()
    {
        $product1 = $this->createProduct('simple_with_family_and_values', [
            new SetCategories(['master']),
            new SetFamily('familyA'),
            new SetTextValue('a_text', null, null, 'Text'),
        ]);
        $product2 = $this->createProduct('simple_with_no_family', [
            new SetCategories(['master']),
            new SetTextValue('a_text', null, null, 'Text'),
        ]);
        $product3 = $this->createProduct('simple_with_no_values', [
            new SetCategories(['master']),
            new SetFamily('familyA')
        ]);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        $valuesWithAText = '{
            "sku": [{
                "locale": null,
                "scope": null,
                "data": "{identifier}"
            }],
            "a_text": [{
                "locale": null,
                "scope": null,
                "data": "Text"
            }]
        }';

        $valuesWithoutAText = '{
            "sku": [{
                "locale": null,
                "scope": null,
                "data": "{identifier}"
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
        $standardizedProducts['simple_with_family_and_values'] = $this->getStandardizedProductsForCompletenesses($product1, '"familyA"', $valuesWithAText, $completenessesFamVal);
        $standardizedProducts['simple_with_no_family'] = $this->getStandardizedProductsForCompletenesses($product2, 'null', $valuesWithAText, '[]');
        $standardizedProducts['simple_with_no_values'] = $this->getStandardizedProductsForCompletenesses($product3, '"familyA"', $valuesWithoutAText, $completenessesNoVal);

        $client = $this->createAuthenticatedClient();
        $search = '{"sku":[{"operator":"IN","value":["simple_with_family_and_values","simple_with_no_family","simple_with_no_values"]}]}';
        $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);
        $client->request('GET', "/api/rest/v1/products-uuid?with_completenesses=true&search=$searchEncoded");

        $expected = <<<JSON
{
    "_links"       : {
        "self"  : {"href" : "http://localhost/api/rest/v1/products-uuid?page=1&with_count=false&pagination_type=page&limit=10&search=$searchEncoded&with_completenesses=true"},
        "first" : {"href" : "http://localhost/api/rest/v1/products-uuid?page=1&with_count=false&pagination_type=page&limit=10&search=$searchEncoded&with_completenesses=true"}
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

        $this->assertListResponse($client->getResponse(), $expected, true);
    }

    public function testListProductsWithSearchOnDateAttributesWithNegativeTimeZoneOffset()
    {
        $standardizedProducts = $this->getStandardizedProducts();
        $client = $this->createAuthenticatedClient();

        date_default_timezone_set('America/Los_Angeles');

        $currentDate = (new \DateTime('now'))->modify("+ 30 minutes")->format('Y-m-d H:i:s');

        $search = sprintf('{"updated":[{"operator":"<","value":"%s"}]}', $currentDate);
        $client->request('GET', 'api/rest/v1/products-uuid?pagination_type=page&limit=10&search=' . $search);
        $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);
        $expected = <<<JSON
{
    "_links"       : {
        "self"  : {"href" : "http://localhost/api/rest/v1/products-uuid?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"},
        "first" : {"href" : "http://localhost/api/rest/v1/products-uuid?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"}
    },
    "current_page" : 1,
    "_embedded"    : {
        "items" : [
            {$standardizedProducts['china']},
            {$standardizedProducts['localizable']},
            {$standardizedProducts['localizable_and_scopable']},
            {$standardizedProducts['scopable']},
            {$standardizedProducts['simple']},
            {$standardizedProducts['with_parent']},
            {$standardizedProducts['without_category']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected, true);
    }

    public function testListProductsUpdatedSinceLastNDays()
    {
        $standardizedProducts = $this->getStandardizedProducts();
        $client = $this->createAuthenticatedClient();

        $search = sprintf('{"updated":[{"operator":"%s","value":4}]}', Operators::SINCE_LAST_N_DAYS);
        $client->request('GET', 'api/rest/v1/products-uuid?pagination_type=page&limit=10&search=' . $search);
        $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);
        $expected = <<<JSON
{
    "_links"       : {
        "self"  : {"href" : "http://localhost/api/rest/v1/products-uuid?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"},
        "first" : {"href" : "http://localhost/api/rest/v1/products-uuid?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"}
    },
    "current_page" : 1,
    "_embedded"    : {
        "items" : [
            {$standardizedProducts['china']},
            {$standardizedProducts['localizable']},
            {$standardizedProducts['localizable_and_scopable']},
            {$standardizedProducts['scopable']},
            {$standardizedProducts['simple']},
            {$standardizedProducts['with_parent']},
            {$standardizedProducts['without_category']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected, true);
    }

    public function testOffsetPaginationListProductsWithMultiplePQBFilters()
    {
        $client = $this->createAuthenticatedClient();

        $search = '{"categories":[{"operator":"IN","value":["categoryB"]}],"a_yes_no":[{"operator":"=","value":true}]}';
        $client->request('GET', 'api/rest/v1/products-uuid?pagination_type=page&search=' . $search);
        $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href" : "http://localhost/api/rest/v1/products-uuid?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"},
        "first" : {"href" : "http://localhost/api/rest/v1/products-uuid?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"}
    },
    "current_page" : 1,
    "_embedded"    : {
        "items" : []
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected, true);
    }

    public function testListProductsWithCompletenessPQBFilters()
    {
        $client = $this->createAuthenticatedClient();

        $search = '{"completeness":[{"operator":"GREATER THAN ON ALL LOCALES","value":50,"locales":["fr_FR"],"scope":"ecommerce"}],"categories":[{"operator":"IN","value":["categoryB"]}],"a_yes_no":[{"operator":"=","value":true}]}';
        $client->request('GET', 'api/rest/v1/products-uuid?search=' . $search);
        $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href" : "http://localhost/api/rest/v1/products-uuid?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"},
        "first" : {"href" : "http://localhost/api/rest/v1/products-uuid?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"}
    },
    "current_page" : 1,
    "_embedded"    : {
        "items" : []
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected, true);
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

        $client->request('GET', 'api/rest/v1/products-uuid?pagination_type=search_after');
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href": "http://localhost/api/rest/v1/products-uuid?with_count=false&pagination_type=search_after&limit=10"},
        "first" : {"href": "http://localhost/api/rest/v1/products-uuid?with_count=false&pagination_type=search_after&limit=10"}
    },
    "_embedded" : {
        "items" : [
            {$standardizedProducts['china']},
            {$standardizedProducts['localizable']},
            {$standardizedProducts['localizable_and_scopable']},
            {$standardizedProducts['scopable']},
            {$standardizedProducts['simple']},
            {$standardizedProducts['with_parent']},
            {$standardizedProducts['without_category']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected, true);
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

        $sortedProducts = \array_values($this->products);
        \usort($sortedProducts, fn (ProductInterface $p1, ProductInterface $p2): int => \strcmp($p1->getUuid()->toString(), $p2->getUuid()->toString()));

        $searchAfterIndex = 1;

        $client->request('GET', sprintf(
            'api/rest/v1/products-uuid?pagination_type=search_after&limit=3&search_after=%s',
            $sortedProducts[$searchAfterIndex]->getUuid()->toString()
        ));

        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href": "http://localhost/api/rest/v1/products-uuid?with_count=false&pagination_type=search_after&limit=3&search_after={searchAfterProductUuid}"},
        "first" : {"href": "http://localhost/api/rest/v1/products-uuid?with_count=false&pagination_type=search_after&limit=3"},
        "next"  : {"href": "http://localhost/api/rest/v1/products-uuid?with_count=false&pagination_type=search_after&limit=3&search_after={lastProductUuid}"}
    },
    "_embedded"    : {
        "items" : [
            {$standardizedProducts[$sortedProducts[$searchAfterIndex + 1]->getIdentifier()]},
            {$standardizedProducts[$sortedProducts[$searchAfterIndex + 2]->getIdentifier()]},
            {$standardizedProducts[$sortedProducts[$searchAfterIndex + 3]->getIdentifier()]}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), \strtr($expected, [
            '{searchAfterProductUuid}' => $sortedProducts[$searchAfterIndex]->getUuid()->toString(),
            '{lastProductUuid}' => $sortedProducts[$searchAfterIndex + 3]->getUuid()->toString(),
        ]), false);

        // check that the search_after param is case-insensitive
        $client->restart();
        $client->request('GET', sprintf(
            'api/rest/v1/products-uuid?pagination_type=search_after&limit=3&search_after=%s',
            \mb_strtoupper($sortedProducts[$searchAfterIndex]->getUuid()->toString())
        ));

        $this->assertListResponse($client->getResponse(), \strtr($expected, [
            '{searchAfterProductUuid}' => \mb_strtoupper($sortedProducts[$searchAfterIndex]->getUuid()->toString()),
            '{lastProductUuid}' => $sortedProducts[$searchAfterIndex + 3]->getUuid()->toString(),
        ]), false);
    }

    public function testSearchAfterPaginationLastPageOfTheListOfProducts()
    {
        $standardizedProducts = $this->getStandardizedProducts();
        $client = $this->createAuthenticatedClient();

        $sortedProducts = \array_values($this->products);
        \usort($sortedProducts, fn (ProductInterface $p1, ProductInterface $p2): int => \strcmp($p1->getUuid()->toString(), $p2->getUuid()->toString()));

        $searchAfterIndex = 2;

        $client->request('GET', sprintf(
            'api/rest/v1/products-uuid?pagination_type=search_after&limit=5&search_after=%s',
            $sortedProducts[$searchAfterIndex]->getUuid()->toString()
        ));
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href": "http://localhost/api/rest/v1/products-uuid?with_count=false&pagination_type=search_after&limit=5&search_after={searchAfterProductUuid}"},
        "first" : {"href": "http://localhost/api/rest/v1/products-uuid?with_count=false&pagination_type=search_after&limit=5"}
    },
    "_embedded"    : {
        "items" : [
            {$standardizedProducts[$sortedProducts[$searchAfterIndex + 1]->getIdentifier()]},
            {$standardizedProducts[$sortedProducts[$searchAfterIndex + 2]->getIdentifier()]},
            {$standardizedProducts[$sortedProducts[$searchAfterIndex + 3]->getIdentifier()]},
            {$standardizedProducts[$sortedProducts[$searchAfterIndex + 4]->getIdentifier()]}
        ]
    }
}
JSON;

        $expected = \strtr($expected, [
            '{searchAfterProductUuid}' => $sortedProducts[$searchAfterIndex]->getUuid()->toString(),
        ]);

        $this->assertListResponse($client->getResponse(), $expected, true);
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
            $client->request('GET', 'api/rest/v1/products-uuid?search=' . $search);
            $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);
            $expected = <<<JSON
{
    "_links": {
        "self"  : {"href" : "http://localhost/api/rest/v1/products-uuid?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"},
        "first" : {"href" : "http://localhost/api/rest/v1/products-uuid?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"}
    },
    "current_page" : 1,
    "_embedded"    : {
        "items" : [
            {$standardizedProducts['with_parent']}
        ]
    }
}
JSON;

            $this->assertListResponse($client->getResponse(), $expected, true);
        }
    }

    public function testListProductsWithUuidFilter(): void
    {
        $standardizedProducts = $this->getStandardizedProducts();

        $search = \json_encode([
            'uuid' => [
                [
                    'operator' => 'IN',
                    'value' => [
                        $this->products['simple']->getUuid()->toString(),
                        $this->products['localizable']->getUuid()->toString(),
                        Uuid::uuid4()->toString(),
                        $this->products['with_parent']->getUuid()->toString(),
                    ],
                ],
            ],
        ]);
        $client = $this->createAuthenticatedClient();
        $client->request('GET', 'api/rest/v1/products-uuid?search=' . $search);
        $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href" : "http://localhost/api/rest/v1/products-uuid?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"},
        "first" : {"href" : "http://localhost/api/rest/v1/products-uuid?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"}
    },
    "current_page" : 1,
    "_embedded"    : {
        "items" : [
            {$standardizedProducts['localizable']},
            {$standardizedProducts['simple']},
            {$standardizedProducts['with_parent']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected, true);
    }

    public function testListProductsWithUuidNotInFilter(): void
    {
        $standardizedProducts = $this->getStandardizedProducts();

        $search = \json_encode([
            'uuid' => [
                [
                    'operator' => 'NOT IN',
                    'value' => [
                        $this->products['simple']->getUuid()->toString(),
                        $this->products['localizable']->getUuid()->toString(),
                        $this->products['with_parent']->getUuid()->toString(),
                    ],
                ],
            ],
        ]);
        $client = $this->createAuthenticatedClient();
        $client->request('GET', 'api/rest/v1/products-uuid?search=' . $search);
        $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href" : "http://localhost/api/rest/v1/products-uuid?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"},
        "first" : {"href" : "http://localhost/api/rest/v1/products-uuid?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"}
    },
    "current_page" : 1,
    "_embedded"    : {
        "items" : [
            {$standardizedProducts['scopable']},
            {$standardizedProducts['localizable_and_scopable']},
            {$standardizedProducts['china']},
            {$standardizedProducts['without_category']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected, true);
    }

    public function testListProductsWithoutParent()
    {
        $standardizedProducts = $this->getStandardizedProducts();
        $client = $this->createAuthenticatedClient();

        $search = '{"parent":[{"operator":"EMPTY","value":null}]}';
        $client->request('GET', 'api/rest/v1/products-uuid?search=' . $search);
        $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href" : "http://localhost/api/rest/v1/products-uuid?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"},
        "first" : {"href" : "http://localhost/api/rest/v1/products-uuid?page=1&with_count=false&pagination_type=page&limit=10&search=${searchEncoded}"}
    },
    "current_page" : 1,
    "_embedded"    : {
        "items" : [
            {$standardizedProducts['china']},
            {$standardizedProducts['localizable']},
            {$standardizedProducts['localizable_and_scopable']},
            {$standardizedProducts['scopable']},
            {$standardizedProducts['simple']},
            {$standardizedProducts['without_category']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected, true);
    }

    public function testAccessDeniedWhenRetrievingProductsWithoutTheAcl()
    {
        $client = $this->createAuthenticatedClient();
        $this->removeAclFromRole('action:pim_api_product_list');

        $client->request('GET', 'api/rest/v1/products-uuid');
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    private function getStandardizedProducts(bool $withAttributeOptions = false): array
    {
        $standardizedProducts['simple'] = file_get_contents(__DIR__ . '/product_simple.json');
        $standardizedProducts['localizable'] = file_get_contents(__DIR__ . '/product_localizable.json');
        $standardizedProducts['scopable'] = file_get_contents(__DIR__ . '/product_scopable.json');
        $standardizedProducts['localizable_and_scopable'] = file_get_contents(__DIR__ . '/product_localizable_and_scopable.json');
        $standardizedProducts['china'] = file_get_contents(__DIR__ . '/product_china.json');
        $standardizedProducts['without_category'] = file_get_contents(__DIR__ . '/product_without_category.json');
        $standardizedProducts['with_parent'] = file_get_contents(__DIR__ . '/product_with_parent.json');

        if ($withAttributeOptions) {
            $original = '"a_simple_select": [{ "locale": null, "scope": null, "data": "optionA" }]';
            $replace = '"a_simple_select": [
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
            ]';

            $standardizedProducts['with_parent'] = \strtr(
                $standardizedProducts['with_parent'],
                [$original => $replace]
            );
        }

        foreach (array_keys($standardizedProducts) as $productIdentifier) {
            $standardizedProducts[$productIdentifier] = \strtr(
                $standardizedProducts[$productIdentifier],
                ['{uuid}' => $this->products[$productIdentifier]->getUuid()->toString()]
            );
        }

        return $standardizedProducts;
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getStandardizedProductsForQualityScore(ProductInterface $product, string $family, string $values, string $qualityScores)
    {
        $uuid = $product->getUuid()->toString();
        $values = \strtr($values, ['{identifier}' => $product->getIdentifier()]);

        return <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/products-uuid/$uuid"
        }
    },
    "uuid": "$uuid",
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

    private function getStandardizedProductsForCompletenesses(ProductInterface $product, string $family, string $values, string $completenesses)
    {
        $uuid = $product->getUuid()->toString();
        $values = \strtr($values, ['{identifier}' => $product->getIdentifier()]);

        return <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/products-uuid/$uuid"
        }
    },
    "uuid": "$uuid",
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
