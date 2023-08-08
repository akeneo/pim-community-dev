<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\WorkOrganization\EndToEnd\Workflow\PublishedProduct;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\PriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetImageValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMeasurementValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetPriceCollectionValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetPriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Doctrine\Common\Collections\Collection;

class SuccessListPublishedProductEndToEnd extends AbstractPublishedProductTestCase
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
        $product1 = $this->createProduct('simple', [
            new SetCategories(['master']),
            new SetMeasurementValue('a_metric', null, null, 10, 'KILOWATT'),
            new SetTextValue('a_text', null, null, 'Text'),
        ]);

        // localizable, categorized in 1 tree (master)
        $product2 = $this->createProduct('localizable', [
            new SetCategories(['categoryB']),
            new SetImageValue('a_localizable_image', null, 'en_US', $this->getFileInfoKey($this->getFixturePath('akeneo.jpg'))),
            new SetImageValue('a_localizable_image', null, 'fr_FR', $this->getFileInfoKey($this->getFixturePath('akeneo.jpg'))),
            new SetImageValue('a_localizable_image', null, 'de_DE', $this->getFileInfoKey($this->getFixturePath('akeneo.jpg'))),
        ]);

        $eurPrice = new PriceValue('10.50', 'EUR');
        $usdPrice = new PriceValue('11.50', 'USD');
        // scopable, categorized in 1 tree (master)
        $product3 = $this->createProduct('scopable', [
            new SetCategories(['categoryA1', 'categoryA2']),
            new SetPriceCollectionValue('a_scopable_price', 'ecommerce', null, [$eurPrice, $usdPrice]),
            new SetPriceCollectionValue('a_scopable_price', 'tablet', null, [$eurPrice, $usdPrice]),
        ]);

        // localizable & scopable, categorized in 2 trees (master and master_china)
        $product4 = $this->createProduct('localizable_and_scopable', [
            new SetCategories(['categoryA', 'master_china']),
            new SetTextareaValue('a_localized_and_scopable_text_area', 'ecommerce', 'en_US', 'Big description'),
            new SetTextareaValue('a_localized_and_scopable_text_area', 'tablet', 'en_US', 'Medium description'),
            new SetTextareaValue('a_localized_and_scopable_text_area', 'tablet', 'de_DE', 'Tolle Beschreibung'),
            new SetTextareaValue('a_localized_and_scopable_text_area', 'tablet', 'fr_FR', 'Description moyenne'),
            new SetTextareaValue('a_localized_and_scopable_text_area', 'ecommerce_china', 'zh_CN', 'China description'),
        ]);

        $product5 = $this->createProduct('product_china', [
            new SetCategories(['master_china']),
        ]);

        $product6 = $this->createProduct('product_without_category2', [
            new SetBooleanValue('a_yes_no', null, null, true),
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
    public function testDefaultPaginationListPublishedProductsWithoutParameter(): void
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

    public function testDefaultPaginationFirstPageListPublishedProductsWithCount(): void
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

    public function testDefaultPaginationLastPageListPublishedProductsWithCount(): void
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
     * Then only published products in "master" tree are returned.
     */
    public function testOffsetPaginationListPublishedProductsWithEcommerceChannel(): void
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
                "associations"  : {},
                "quantified_associations": {}
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
                                {"amount" : "10.50", "currency" : "EUR"},
                                {"amount" : "11.50", "currency" : "USD"}
                            ]
                        }
                    ]
                },
                "created"       : "2017-01-23T11:44:25+01:00",
                "updated"       : "2017-01-23T11:44:25+01:00",
                "associations"  : {},
                "quantified_associations": {}
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
                "associations"  : {},
                "quantified_associations": {}
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
     * Then only published products in "master" tree are returned.
     */
    public function testOffsetPaginationListPublishedProductsWithTabletChannel(): void
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
                                {"amount" : "10.50", "currency" : "EUR"},
                                {"amount" : "11.50", "currency" : "USD"}
                            ]
                        }
                    ]
                },
                "created"       : "2017-01-23T11:44:25+01:00",
                "updated"       : "2017-01-23T11:44:25+01:00",
                "associations"  : {},
                "quantified_associations": {}
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
                        {"locale" : "de_DE", "scope" : "tablet", "data" : "Tolle Beschreibung"},
                        {"locale" : "en_US", "scope" : "tablet", "data" : "Medium description"},
                        {"locale" : "fr_FR", "scope" : "tablet", "data" : "Description moyenne"}
                    ]
                },
                "created"       : "2017-01-23T11:44:25+01:00",
                "updated"       : "2017-01-23T11:44:25+01:00",
                "associations"  : {},
                "quantified_associations": {}
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
     * Then only products in "master" tree are returned.
     */
    public function testOffsetPaginationListProductsWithTabletChannelAndFRLocale(): void
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
                "associations"  : {},
                "quantified_associations": {}
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
                                {"amount" : "10.50", "currency" : "EUR"},
                                {"amount" : "11.50", "currency" : "USD"}
                            ]
                        }
                    ]
                },
                "created"       : "2017-01-23T11:44:25+01:00",
                "updated"       : "2017-01-23T11:44:25+01:00",
                "associations"  : {},
                "quantified_associations": {}
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
                "associations"  : {},
                "quantified_associations": {}
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
     * Then only published products in "master_china" tree are returned.
     */
    public function testOffsetPaginationListPublishedProductsWithEcommerceChinaChannel(): void
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
                "associations"  : {},
                "quantified_associations": {}
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
                "associations"  : {},
                "quantified_associations": {}
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
     * Then we return all products (whatever the categories).
     */
    public function testOffsetPaginationListProductsWithENAndCNLocales(): void
    {
        $standardizedPublishedProducts = $this->getStandardizedPublishedProducts();
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/published-products?locales=en_US,zh_CN&pagination_type=page');
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href" : "http://localhost/api/rest/v1/published-products?page=1&with_count=false&pagination_type=page&limit=10&locales=en_US,zh_CN"},
        "first" : {"href" : "http://localhost/api/rest/v1/published-products?page=1&with_count=false&pagination_type=page&limit=10&locales=en_US,zh_CN"}
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
                                {"amount" : "10.50", "currency" : "EUR"},
                                {"amount" : "11.50", "currency" : "USD"}
                            ]
                        },
                        {
                            "locale" : null,
                            "scope"  : "ecommerce",
                            "data"   : [
                                {"amount" : "10.50", "currency" : "EUR"},
                                {"amount" : "11.50", "currency" : "USD"}
                            ]
                        }
                    ]
                },
                "created"       : "2017-01-23T11:44:25+01:00",
                "updated"       : "2017-01-23T11:44:25+01:00",
                "associations"  : {},
                "quantified_associations": {}
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
                "associations"  : [],
                "quantified_associations": {}
            },
            {$standardizedPublishedProducts['product_china']},
            {$standardizedPublishedProducts['product_without_category2']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testOffsetPaginationListProductsWithFilteredAttributes(): void
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
                "associations"  : {},
                "quantified_associations": {}
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
                "associations"  : {},
                "quantified_associations": {}
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
                "associations"  : {},
                "quantified_associations": {}
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
                "associations"  : {},
                "quantified_associations": {}
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
                "associations"  : {},
                "quantified_associations": {}
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
                "associations"  : {},
                "quantified_associations": {}
            }
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testOffsetPaginationListProductsWithChannelLocalesAndAttributesParams(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/published-products?scope=tablet&locales=fr_FR&attributes=a_scopable_price,a_metric,a_localized_and_scopable_text_area&pagination_type=page');
        $expected = <<<JSON
{
    "_links"       : {
        "self"  : {"href" : "http://localhost/api/rest/v1/published-products?page=1&with_count=false&pagination_type=page&limit=10&scope=tablet&locales=fr_FR&attributes=a_scopable_price,a_metric,a_localized_and_scopable_text_area"},
        "first" : {"href" : "http://localhost/api/rest/v1/published-products?page=1&with_count=false&pagination_type=page&limit=10&scope=tablet&locales=fr_FR&attributes=a_scopable_price,a_metric,a_localized_and_scopable_text_area"}
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
                "associations"  : {},
                "quantified_associations": {}
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
                "associations"  : {},
                "quantified_associations": {}
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
                                {"amount" : "10.50", "currency" : "EUR"},
                                {"amount" : "11.50", "currency" : "USD"}
                            ]
                        }
                    ]
                },
                "created"       : "2017-01-23T11:44:25+01:00",
                "updated"       : "2017-01-23T11:44:25+01:00",
                "associations"  : {},
                "quantified_associations": {}
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
                "associations"  : {},
                "quantified_associations": {}
            }
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testTheSecondPageOfTheListOfProductsWithOffsetPaginationWithoutCount(): void
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
                "associations"  : {},
                "quantified_associations": {}
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
                "associations"  : {},
                "quantified_associations": {}
            }
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testOutOfRangeProductsList(): void
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

    public function testOffsetPaginationListProductsWithSearch(): void
    {
        $client = $this->createAuthenticatedClient();

        $search = '{"a_metric":[{"operator":">","value":{"amount":"9","unit":"KILOWATT"}}]}';
        $client->request('GET', 'api/rest/v1/published-products?pagination_type=page&search=' . $search);
        $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);
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
                "associations"  : {},
                "quantified_associations": {}
            }
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testOffsetPaginationListProductsWithMultiplePQBFilters(): void
    {
        $client = $this->createAuthenticatedClient();

        $search = '{"categories":[{"operator":"IN", "value":["categoryB"]}], "a_yes_no":[{"operator":"=","value":true}]}';
        $client->request('GET', 'api/rest/v1/published-products?pagination_type=page&search=' . $search);
        $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);
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

    public function testListProductsWithCompletenessPQBFilters(): void
    {
        $client = $this->createAuthenticatedClient();

        $search = '{"completeness":[{"operator":"GREATER THAN ON ALL LOCALES","value":50,"locales":["fr_FR"],"scope":"ecommerce"}],"categories":[{"operator":"IN", "value":["categoryB"]}], "a_yes_no":[{"operator":"=","value":true}]}';
        $client->request('GET', 'api/rest/v1/published-products?search=' . $search);
        $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);
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
     * Get all products, whatever locale, scope, category with a search after pagination.
     */
    public function testSearchAfterPaginationListProductsWithoutParameter(): void
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
            {$standardizedPublishedProducts['localizable']},
            {$standardizedPublishedProducts['localizable_and_scopable']},
            {$standardizedPublishedProducts['product_china']},
            {$standardizedPublishedProducts['product_without_category2']},
            {$standardizedPublishedProducts['scopable']},
            {$standardizedPublishedProducts['simple']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testSearchAfterPaginationListProductsWithNextLink(): void
    {
        $standardizedPublishedProducts = $this->getStandardizedPublishedProducts();
        $client = $this->createAuthenticatedClient();

        $client->request('GET', sprintf('api/rest/v1/published-products?pagination_type=search_after&limit=3&search_after=%s', 'localizable_and_scopable'));
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href": "http://localhost/api/rest/v1/published-products?pagination_type=search_after&limit=3&search_after=localizable_and_scopable"},
        "first" : {"href": "http://localhost/api/rest/v1/published-products?pagination_type=search_after&limit=3"},
        "next"  : {"href": "http://localhost/api/rest/v1/published-products?pagination_type=search_after&limit=3&search_after=scopable"}
    },
    "_embedded"    : {
        "items" : [
            {$standardizedPublishedProducts['product_china']},
            {$standardizedPublishedProducts['product_without_category2']},
            {$standardizedPublishedProducts['scopable']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testSearchAfterPaginationLastPageOfTheListOfProducts(): void
    {
        $standardizedPublishedProducts = $this->getStandardizedPublishedProducts();
        $client = $this->createAuthenticatedClient();

        $client->request('GET', sprintf('api/rest/v1/published-products?pagination_type=search_after&limit=4&search_after=%s', 'product_china'));
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href": "http://localhost/api/rest/v1/published-products?pagination_type=search_after&limit=4&search_after=product_china"},
        "first" : {"href": "http://localhost/api/rest/v1/published-products?pagination_type=search_after&limit=4"}
    },
    "_embedded"    : {
        "items" : [
            {$standardizedPublishedProducts['product_without_category2']},
            {$standardizedPublishedProducts['scopable']},
            {$standardizedPublishedProducts['simple']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testListPublishedProductWithAssociatedPublishedProduct(): void {
        $product = $this->createProduct('published_product_with_associated_published_product', [
            new AssociateProducts('X_SELL', ['simple']),
        ]);
        $this->publishProduct($product);

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
            {$standardizedPublishedProducts['product_without_category2']},
            {$standardizedPublishedProducts['published_product_with_associated_published_product']}
		]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    /**
     * @return array
     */
    protected function getStandardizedPublishedProducts(): array
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
    "associations": {},
    "quantified_associations": []
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
    "associations": {},
    "quantified_associations": []
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
    "associations": {},
    "quantified_associations": []
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
            "locale": "de_DE",
            "scope": "tablet",
            "data": "Tolle Beschreibung"
        }, {
            "locale": "zh_CN",
            "scope": "ecommerce_china",
            "data": "China description"
        }
        ]
    },
    "created": "2017-03-11T10:39:38+01:00",
    "updated": "2017-03-11T10:39:38+01:00",
    "associations": {},
    "quantified_associations": []
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
   "associations": {},
   "quantified_associations": []
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
    "associations": {},
    "quantified_associations": []
}
JSON;

        $standardizedPublishedProducts['published_product_with_associated_published_product'] = <<<JSON
{
   "_links": {
       "self": {
           "href": "http://localhost/api/rest/v1/published-products/published_product_with_associated_published_product"
       }
   },
   "identifier": "published_product_with_associated_published_product",
   "family": null,
   "groups": [],
   "categories": [],
   "enabled": true,
   "values": {},
   "created": "2017-03-11T10:39:38+01:00",
   "updated": "2017-03-11T10:39:38+01:00",
   "associations": {"PACK":{"groups":[],"products":[],"product_models":[]},"SUBSTITUTION":{"groups":[],"products":[],"product_models":[]},"UPSELL":{"groups":[],"products":[],"product_models":[]},"X_SELL":{"groups":[],"products":["simple"],"product_models":[]}},
   "quantified_associations": []
}
JSON;

        return $standardizedPublishedProducts;
    }
}
