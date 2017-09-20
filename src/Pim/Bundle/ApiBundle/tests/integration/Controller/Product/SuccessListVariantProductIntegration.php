<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\Product;

use Akeneo\Test\Integration\Configuration;
use Doctrine\Common\Collections\Collection;

class SuccessListVariantProductIntegration extends AbstractProductTestCase
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
        $this->createVariantProduct('apollon_blue_s', [
            'categories' => ['master'],
            'parent' => 'amor',
            'values' => [
                'size' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 's',
                    ],
                ],
            ],
        ]);

        // apollon_blue_m, categorized in 1 tree (master)
        $this->createVariantProduct('apollon_blue_m', [
            'categories' => ['master_accessories'],
            'parent' => 'amor',
            'values' => [
                'color' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'blue',
                    ],
                ],
            ],
        ]);

        // apollon_blue_l, categorized in 1 tree (master)
        $this->createVariantProduct('apollon_blue_l', [
            'categories' => ['master_men_blazers_deals', 'master_accessories_hats'],
            'parent' => 'amor',
            'values' => [
                'size' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'l',
                    ],
                ],
            ],
        ]);

        // apollon_blue_m & apollon_blue_l, categorized in 2 trees (master and master_women_blouses_deals)
        $this->createVariantProduct('apollon_blue_xl', [
            'categories' => ['suppliers', 'master_women_blouses_deals'],
            'parent' => 'amor',
            'values' => [
                'size' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'xl',
                    ],
                ],
            ],
        ]);

        $this->createVariantProduct('apollon_blue_xxl', [
            'categories' => ['master_women_blouses_deals'],
            'parent' => 'amor',
        ]);

        $this->createVariantProduct('apollon_blue_xs', [
            'parent' => 'amor',
            'values' => [
                'size' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'xs',
                    ],
                ],
            ],
        ]);

        $this->products = $this->get('pim_catalog.repository.product')->findAll();
    }

    /**
     * Get all products, whatever locale, scope, category with the default pagination type that is with an offset.
     */
    public function testDefaultPaginationListProductsWithoutParameter()
    {
        $standardizedProducts = $this->getStandardizedProducts();
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products');
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href": "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=10"},
        "first" : {"href": "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=10"}
    },
    "current_page" : 1,
    "_embedded"    : {
		"items": [
            {$standardizedProducts['apollon_blue_s']},
            {$standardizedProducts['apollon_blue_m']},
            {$standardizedProducts['apollon_blue_l']},
            {$standardizedProducts['apollon_blue_xl']},
            {$standardizedProducts['apollon_blue_xxl']},
            {$standardizedProducts['apollon_blue_xs']}
		]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
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
    "items_count"  : 6,
    "_embedded"    : {
		"items": [
            {$standardizedProducts['apollon_blue_s']},
            {$standardizedProducts['apollon_blue_m']},
            {$standardizedProducts['apollon_blue_l']}
		]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testDefaultPaginationLastPageListProductsWithCount()
    {
        $standardizedProducts = $this->getStandardizedProducts();
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products?with_count=true&limit=3&page=2');
        $expected = <<<JSON
{
    "_links": {
        "self"     : {"href": "http://localhost/api/rest/v1/products?page=2&with_count=true&pagination_type=page&limit=3"},
        "first"    : {"href": "http://localhost/api/rest/v1/products?page=1&with_count=true&pagination_type=page&limit=3"},
        "previous" : {"href": "http://localhost/api/rest/v1/products?page=1&with_count=true&pagination_type=page&limit=3"},
        "next"     : {"href": "http://localhost/api/rest/v1/products?page=3&with_count=true&pagination_type=page&limit=3"}
    },
    "current_page" : 2,
    "items_count"  : 6,
    "_embedded"    : {
		"items": [
            {$standardizedProducts['apollon_blue_xl']},
            {$standardizedProducts['apollon_blue_xxl']},
            {$standardizedProducts['apollon_blue_xs']}
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
     * Then only products in "master" tree are returned
     */
    public function testOffsetPaginationListProductsWithEcommerceChannel()
    {
        $standardizedProducts = $this->getStandardizedProducts();
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products?scope=ecommerce&pagination_type=page');
        $expected = <<<JSON
{
    "_links"       : {
        "self"  : {"href" : "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=10&scope=ecommerce"},
        "first" : {"href" : "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=10&scope=ecommerce"}
    },
    "current_page" : 1,
    "_embedded"    : {
        "items" : [
            {$standardizedProducts['apollon_blue_s']},
            {$standardizedProducts['apollon_blue_m']},
            {$standardizedProducts['apollon_blue_l']},
            {$standardizedProducts['apollon_blue_xl']},
            {$standardizedProducts['apollon_blue_xxl']}
		]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testOffsetPaginationListProductsWithFilteredAttributes()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products?attributes=color&pagination_type=page');
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href" : "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=10&attributes=color"},
        "first" : {"href" : "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=10&attributes=color"}
    },
    "current_page" : 1,
    "_embedded"    : {
        "items" : [
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/products/apollon_blue_s"}
                },
                "identifier"    : "apollon_blue_s",
                "family"        : "clothing",
                "parent"        : "amor",
                "groups"        : [],
                "variant_group" : null,
                "categories"    : ["master"],
                "enabled"       : true,
                "values"        : {},
                "created"       : "2017-01-23T11:44:25+01:00",
                "updated"       : "2017-01-23T11:44:25+01:00",
                "associations"  : {}
            },
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/products/apollon_blue_m"}
                },
                "identifier"    : "apollon_blue_m",
                "family"        : "clothing",
                "parent"        : "amor",
                "groups"        : [],
                "variant_group" : null,
                "categories"    : ["master_accessories"],
                "enabled"       : true,
                "values": {
                    "color": [{
                        "locale": null,
                        "scope": null,
                        "data": "blue"
                    }]
                },
                "created"       : "2017-01-23T11:44:25+01:00",
                "updated"       : "2017-01-23T11:44:25+01:00",
                "associations"  : {}
            },
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/products/apollon_blue_l"}
                },
                "identifier"    : "apollon_blue_l",
                "family"        : "clothing",
                "parent"        : "amor",
                "groups"        : [],
                "variant_group" : null,
                "categories"    : ["master_accessories_hats", "master_men_blazers_deals"],
                "enabled"       : true,
                "values"        : {},
                "created"       : "2017-01-23T11:44:25+01:00",
                "updated"       : "2017-01-23T11:44:25+01:00",
                "associations"  : {}
            },
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/products/apollon_blue_xl"}
                },
                "identifier"    : "apollon_blue_xl",
                "family"        : "clothing",
                "parent"        : "amor",
                "groups"        : [],
                "variant_group" : null,
                "categories"    : ["master_women_blouses_deals", "suppliers"],
                "enabled"       : true,
                "values"        : {},
                "created"       : "2017-01-23T11:44:25+01:00",
                "updated"       : "2017-01-23T11:44:25+01:00",
                "associations"  : {}
            },
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/products/apollon_blue_xxl"}
                },
                "identifier"    : "apollon_blue_xxl",
                "family"        : "clothing",
                "parent"        : "amor",
                "groups"        : [],
                "variant_group" : null,
                "categories"    : ["master_women_blouses_deals"],
                "enabled"       : true,
                "values"        : {},
                "created"       : "2017-01-23T11:44:25+01:00",
                "updated"       : "2017-01-23T11:44:25+01:00",
                "associations"  : {}
            },
            {
                "_links" : {
                    "self" : {"href" : "http://localhost/api/rest/v1/products/apollon_blue_xs"}
                },
                "identifier"    : "apollon_blue_xs",
                "family"        : "clothing",
                "parent"        : "amor",
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

    public function testOffsetPaginationListProductsWithChannelLocalesAndAttributesParams()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products?scope=ecommerce&locales=fr_FR&attributes=size,erp_name,supplier&pagination_type=page');
        $expected = <<<JSON
{
  "_links": {
    "self": {
      "href": "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=10&scope=ecommerce&locales=fr_FR&attributes=size%2Cerp_name%2Csupplier"
    },
    "first": {
      "href": "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=10&scope=ecommerce&locales=fr_FR&attributes=size%2Cerp_name%2Csupplier"
    }
  },
  "current_page": 1,
  "_embedded": {
    "items": [
      {
        "_links": {
          "self": {
            "href": "http://localhost/api/rest/v1/products/apollon_blue_s"
          }
        },
        "identifier": "apollon_blue_s",
        "family": "clothing",
        "parent": "amor",
        "groups": [
          
        ],
        "variant_group": null,
        "categories": [
          "master"
        ],
        "enabled": true,
        "values": {
          "size": [
            {
              "locale": null,
              "scope": null,
              "data": "s"
            }
          ],
          "supplier": [
            {
              "locale": null,
              "scope": null,
              "data": "zaro"
            }
          ]
        },
        "created": "2017-09-20T17:46:20+02:00",
        "updated": "2017-09-20T17:46:20+02:00",
        "associations": {
          
        }
      },
      {
        "_links": {
          "self": {
            "href": "http://localhost/api/rest/v1/products/apollon_blue_m"
          }
        },
        "identifier": "apollon_blue_m",
        "family": "clothing",
        "parent": "amor",
        "groups": [
          
        ],
        "variant_group": null,
        "categories": [
          "master_accessories"
        ],
        "enabled": true,
        "values": {
          "supplier": [
            {
              "locale": null,
              "scope": null,
              "data": "zaro"
            }
          ]
        },
        "created": "2017-09-20T17:46:21+02:00",
        "updated": "2017-09-20T17:46:21+02:00",
        "associations": {
          
        }
      },
      {
        "_links": {
          "self": {
            "href": "http://localhost/api/rest/v1/products/apollon_blue_l"
          }
        },
        "identifier": "apollon_blue_l",
        "family": "clothing",
        "parent": "amor",
        "groups": [
          
        ],
        "variant_group": null,
        "categories": [
          "master_accessories_hats",
          "master_men_blazers_deals"
        ],
        "enabled": true,
        "values": {
          "size": [
            {
              "locale": null,
              "scope": null,
              "data": "l"
            }
          ],
          "supplier": [
            {
              "locale": null,
              "scope": null,
              "data": "zaro"
            }
          ]
        },
        "created": "2017-09-20T17:46:21+02:00",
        "updated": "2017-09-20T17:46:21+02:00",
        "associations": {
          
        }
      },
      {
        "_links": {
          "self": {
            "href": "http://localhost/api/rest/v1/products/apollon_blue_xl"
          }
        },
        "identifier": "apollon_blue_xl",
        "family": "clothing",
        "parent": "amor",
        "groups": [
          
        ],
        "variant_group": null,
        "categories": [
          "master_women_blouses_deals",
          "suppliers"
        ],
        "enabled": true,
        "values": {
          "size": [
            {
              "locale": null,
              "scope": null,
              "data": "xl"
            }
          ],
          "supplier": [
            {
              "locale": null,
              "scope": null,
              "data": "zaro"
            }
          ]
        },
        "created": "2017-09-20T17:46:21+02:00",
        "updated": "2017-09-20T17:46:21+02:00",
        "associations": {
          
        }
      },
      {
        "_links": {
          "self": {
            "href": "http://localhost/api/rest/v1/products/apollon_blue_xxl"
          }
        },
        "identifier": "apollon_blue_xxl",
        "family": "clothing",
        "parent": "amor",
        "groups": [
          
        ],
        "variant_group": null,
        "categories": [
          "master_women_blouses_deals"
        ],
        "enabled": true,
        "values": {
          "supplier": [
            {
              "locale": null,
              "scope": null,
              "data": "zaro"
            }
          ]
        },
        "created": "2017-09-20T17:46:21+02:00",
        "updated": "2017-09-20T17:46:21+02:00",
        "associations": {
          
        }
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

        $client->request('GET', 'api/rest/v1/products?attributes=size&page=2&limit=2&pagination_type=page&with_count=false');
        $expected = <<<JSON
{
  "_links": {
    "self": {
      "href": "http://localhost/api/rest/v1/products?page=2&with_count=false&pagination_type=page&limit=2&attributes=size"
    },
    "first": {
      "href": "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=2&attributes=size"
    },
    "previous": {
      "href": "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=2&attributes=size"
    },
    "next": {
      "href": "http://localhost/api/rest/v1/products?page=3&with_count=false&pagination_type=page&limit=2&attributes=size"
    }
  },
  "current_page": 2,
  "_embedded": {
    "items": [
      {
        "_links": {
          "self": {
            "href": "http://localhost/api/rest/v1/products/apollon_blue_l"
          }
        },
        "identifier": "apollon_blue_l",
        "family": "clothing",
        "parent": "amor",
        "groups": [
          
        ],
        "variant_group": null,
        "categories": [
          "master_accessories_hats",
          "master_men_blazers_deals"
        ],
        "enabled": true,
        "values": {
          "size": [
            {
              "locale": null,
              "scope": null,
              "data": "l"
            }
          ]
        },
        "created": "2017-09-20T17:50:44+02:00",
        "updated": "2017-09-20T17:50:44+02:00",
        "associations": {
          
        }
      },
      {
        "_links": {
          "self": {
            "href": "http://localhost/api/rest/v1/products/apollon_blue_xl"
          }
        },
        "identifier": "apollon_blue_xl",
        "family": "clothing",
        "parent": "amor",
        "groups": [
          
        ],
        "variant_group": null,
        "categories": [
          "master_women_blouses_deals",
          "suppliers"
        ],
        "enabled": true,
        "values": {
          "size": [
            {
              "locale": null,
              "scope": null,
              "data": "xl"
            }
          ]
        },
        "created": "2017-09-20T17:50:44+02:00",
        "updated": "2017-09-20T17:50:44+02:00",
        "associations": {
          
        }
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

        $client->request('GET', 'api/rest/v1/products?page=2&pagination_type=page&with_count=true');
        $expected = <<<JSON
{
    "_links"       : {
        "self"        : {"href" : "http://localhost/api/rest/v1/products?page=2&with_count=true&pagination_type=page&limit=10"},
        "first"       : {"href" : "http://localhost/api/rest/v1/products?page=1&with_count=true&pagination_type=page&limit=10"},
        "previous"    : {"href" : "http://localhost/api/rest/v1/products?page=1&with_count=true&pagination_type=page&limit=10"}
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

        $search = '{"size":[{"operator":"IN","value":["s"]}]}';
        $client->request('GET', 'api/rest/v1/products?pagination_type=page&search=' . $search);
        $searchEncoded = rawurlencode($search);
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
                    "self" : {"href" : "http://localhost/api/rest/v1/products/apollon_blue_s"}
                },
                "identifier"    : "apollon_blue_s",
                "family"        : "clothing",
                "parent"        : "amor",
                "groups"        : [],
                "variant_group" : null,
                "categories"    : ["master"],
                "enabled"       : true,
                "values": {
                "size": [
                  {
                    "locale": null,
                    "scope": null,
                    "data": "s"
                  }
                ],
                "name": [
                  {
                    "locale": "en_US",
                    "scope": null,
                    "data": "Heritage jacket navy"
                  }
                ],
                "price": [
                  {
                    "locale": null,
                    "scope": null,
                    "data": [
                      {
                        "amount": "999.00",
                        "currency": "EUR"
                      }
                    ]
                  }
                ],
                "erp_name": [
                  {
                    "locale": "en_US",
                    "scope": null,
                    "data": "Amor"
                  }
                ],
                "supplier": [
                  {
                    "locale": null,
                    "scope": null,
                    "data": "zaro"
                  }
                ],
                "collection": [
                  {
                    "locale": null,
                    "scope": null,
                    "data": [
                      "summer_2016"
                    ]
                  }
                ],
                "description": [
                  {
                    "locale": "en_US",
                    "scope": "ecommerce",
                    "data": "Heritage jacket navy blue tweed suit with single breasted 2 button. 53% wool, 22% polyester, 18% acrylic, 5% nylon, 1% cotton, 1% viscose. Dry Cleaning uniquement.Le mannequin measuring 1m85 and wears UK size 40, size 50 FR"
                  }
                ],
                "wash_temperature": [
                  {
                    "locale": null,
                    "scope": null,
                    "data": "800"
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

        $search = '{"categories":[{"operator":"IN", "value":["master_accessories"]}], "color":[{"operator":"IN","value":["black"]}]}';
        $client->request('GET', 'api/rest/v1/products?pagination_type=page&search=' . $search);
        $searchEncoded = rawurlencode($search);
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

        $search = '{"completeness":[{"operator":"GREATER THAN ON ALL LOCALES","value":50,"locales":["fr_FR"],"scope":"ecommerce"}],"categories":[{"operator":"IN", "value":["master_accessories"]}], "size":[{"operator":"IN","value":["xl"]}]}';
        $client->request('GET', 'api/rest/v1/products?search=' . $search);
        $searchEncoded = rawurlencode($search);
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
     */
    public function testSearchAfterPaginationListProductsWithoutParameter()
    {
        $standardizedProducts = $this->getStandardizedProducts();
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products?pagination_type=search_after');
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href": "http://localhost/api/rest/v1/products?pagination_type=search_after&limit=10"},
        "first" : {"href": "http://localhost/api/rest/v1/products?pagination_type=search_after&limit=10"}
    },
    "_embedded" : {
        "items" : [
            {$standardizedProducts['apollon_blue_s']},
            {$standardizedProducts['apollon_blue_m']},
            {$standardizedProducts['apollon_blue_l']},
            {$standardizedProducts['apollon_blue_xl']},
            {$standardizedProducts['apollon_blue_xxl']},
            {$standardizedProducts['apollon_blue_xs']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testSearchAfterPaginationListProductsWithNextLink()
    {
        $standardizedProducts = $this->getStandardizedProducts();
        $client = $this->createAuthenticatedClient();

        $id = [
            'apollon_blue_s'  => rawurlencode($this->getEncryptedId('apollon_blue_s')),
            'apollon_blue_m'  => rawurlencode($this->getEncryptedId('apollon_blue_m')),
            'apollon_blue_xl' => rawurlencode($this->getEncryptedId('apollon_blue_xl')),
        ];

        $client->request('GET', sprintf('api/rest/v1/products?pagination_type=search_after&limit=3&search_after=%s', $id['apollon_blue_s']));
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href": "http://localhost/api/rest/v1/products?pagination_type=search_after&limit=3&search_after={$id['apollon_blue_s']}"},
        "first" : {"href": "http://localhost/api/rest/v1/products?pagination_type=search_after&limit=3"},
        "next"  : {"href": "http://localhost/api/rest/v1/products?pagination_type=search_after&limit=3&search_after={$id['apollon_blue_xl']}"}
    },
    "_embedded"    : {
        "items" : [
            {$standardizedProducts['apollon_blue_m']},
            {$standardizedProducts['apollon_blue_l']},
            {$standardizedProducts['apollon_blue_xl']}
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

        $encryptedId = rawurlencode($this->getEncryptedId('apollon_blue_l'));

        $client->request('GET', sprintf('api/rest/v1/products?pagination_type=search_after&limit=4&search_after=%s' , $encryptedId));
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href": "http://localhost/api/rest/v1/products?pagination_type=search_after&limit=4&search_after={$encryptedId}"},
        "first" : {"href": "http://localhost/api/rest/v1/products?pagination_type=search_after&limit=4"}
    },
    "_embedded"    : {
        "items" : [
            {$standardizedProducts['apollon_blue_xl']},
            {$standardizedProducts['apollon_blue_xxl']},
            {$standardizedProducts['apollon_blue_xs']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    /**
     * @param string $productIdentifier
     */
    private function getEncryptedId($productIdentifier)
    {
        $encrypter = $this->get('pim_api.security.primary_key_encrypter');
        $productRepository = $this->get('pim_catalog.repository.product');

        $product = $productRepository->findOneByIdentifier($productIdentifier);

        return $encrypter->encrypt($product->getId());
    }

    /**
     * @return array
     */
    private function getStandardizedProducts() {
        $standardizedProducts['apollon_blue_s'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/products/apollon_blue_s"
        }
    },
  "identifier": "apollon_blue_s",
  "family": "clothing",
  "parent": "amor",
  "groups": [],
  "variant_group": null,
  "categories": [
    "master"
  ],
  "enabled": true,
  "values": {
    "size": [
      {
        "locale": null,
        "scope": null,
        "data": "s"
      }
    ],
    "name": [
      {
        "locale": "en_US",
        "scope": null,
        "data": "Heritage jacket navy"
      }
    ],
    "price": [
      {
        "locale": null,
        "scope": null,
        "data": [
          {
            "amount": "999.00",
            "currency": "EUR"
          }
        ]
      }
    ],
    "erp_name": [
      {
        "locale": "en_US",
        "scope": null,
        "data": "Amor"
      }
    ],
    "supplier": [
      {
        "locale": null,
        "scope": null,
        "data": "zaro"
      }
    ],
    "collection": [
      {
        "locale": null,
        "scope": null,
        "data": [
          "summer_2016"
        ]
      }
    ],
    "description": [
      {
        "locale": "en_US",
        "scope": "ecommerce",
        "data": "Heritage jacket navy blue tweed suit with single breasted 2 button. 53% wool, 22% polyester, 18% acrylic, 5% nylon, 1% cotton, 1% viscose. Dry Cleaning uniquement.Le mannequin measuring 1m85 and wears UK size 40, size 50 FR"
      }
    ],
    "wash_temperature": [
      {
        "locale": null,
        "scope": null,
        "data": "800"
      }
    ]
  },
  "created": "2017-09-20T15:37:40+02:00",
  "updated": "2017-09-20T15:37:40+02:00",
  "associations": {}
}
JSON;

        $standardizedProducts['apollon_blue_m'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/products/apollon_blue_m"
        }
    },
  "identifier": "apollon_blue_m",
  "family": "clothing",
  "parent": "amor",
  "groups": [],
  "variant_group": null,
  "categories": [
    "master_accessories"
  ],
  "enabled": true,
  "values": {
    "color": [
      {
        "locale": null,
        "scope": null,
        "data": "blue"
      }
    ],
    "name": [
      {
        "locale": "en_US",
        "scope": null,
        "data": "Heritage jacket navy"
      }
    ],
    "price": [
      {
        "locale": null,
        "scope": null,
        "data": [
          {
            "amount": "999.00",
            "currency": "EUR"
          }
        ]
      }
    ],
    "erp_name": [
      {
        "locale": "en_US",
        "scope": null,
        "data": "Amor"
      }
    ],
    "supplier": [
      {
        "locale": null,
        "scope": null,
        "data": "zaro"
      }
    ],
    "collection": [
      {
        "locale": null,
        "scope": null,
        "data": [
          "summer_2016"
        ]
      }
    ],
    "description": [
      {
        "locale": "en_US",
        "scope": "ecommerce",
        "data": "Heritage jacket navy blue tweed suit with single breasted 2 button. 53% wool, 22% polyester, 18% acrylic, 5% nylon, 1% cotton, 1% viscose. Dry Cleaning uniquement.Le mannequin measuring 1m85 and wears UK size 40, size 50 FR"
      }
    ],
    "wash_temperature": [
      {
        "locale": null,
        "scope": null,
        "data": "800"
      }
    ]
  },
  "created": "2017-09-20T15:37:40+02:00",
  "updated": "2017-09-20T15:37:40+02:00",
  "associations": {}
}
JSON;

        $standardizedProducts['apollon_blue_l'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/products/apollon_blue_l"
        }
    },
  "identifier": "apollon_blue_l",
  "family": "clothing",
  "parent": "amor",
  "groups": [],
  "variant_group": null,
  "categories": [
    "master_accessories_hats",
    "master_men_blazers_deals"
  ],
  "enabled": true,
  "values": {
    "size": [
      {
        "locale": null,
        "scope": null,
        "data": "l"
      }
    ],
    "name": [
      {
        "locale": "en_US",
        "scope": null,
        "data": "Heritage jacket navy"
      }
    ],
    "price": [
      {
        "locale": null,
        "scope": null,
        "data": [
          {
            "amount": "999.00",
            "currency": "EUR"
          }
        ]
      }
    ],
    "erp_name": [
      {
        "locale": "en_US",
        "scope": null,
        "data": "Amor"
      }
    ],
    "supplier": [
      {
        "locale": null,
        "scope": null,
        "data": "zaro"
      }
    ],
    "collection": [
      {
        "locale": null,
        "scope": null,
        "data": [
          "summer_2016"
        ]
      }
    ],
    "description": [
      {
        "locale": "en_US",
        "scope": "ecommerce",
        "data": "Heritage jacket navy blue tweed suit with single breasted 2 button. 53% wool, 22% polyester, 18% acrylic, 5% nylon, 1% cotton, 1% viscose. Dry Cleaning uniquement.Le mannequin measuring 1m85 and wears UK size 40, size 50 FR"
      }
    ],
    "wash_temperature": [
      {
        "locale": null,
        "scope": null,
        "data": "800"
      }
    ]
  },
  "created": "2017-09-20T15:37:40+02:00",
  "updated": "2017-09-20T15:37:40+02:00",
  "associations": {}
}
JSON;

        $standardizedProducts['apollon_blue_xl'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/products/apollon_blue_xl"
        }
    },
  "identifier": "apollon_blue_xl",
  "family": "clothing",
  "parent": "amor",
  "groups": [
    
  ],
  "variant_group": null,
  "categories": [
    "master_women_blouses_deals",
    "suppliers"
  ],
  "enabled": true,
  "values": {
    "size": [
      {
        "locale": null,
        "scope": null,
        "data": "xl"
      }
    ],
    "name": [
      {
        "locale": "en_US",
        "scope": null,
        "data": "Heritage jacket navy"
      }
    ],
    "price": [
      {
        "locale": null,
        "scope": null,
        "data": [
          {
            "amount": "999.00",
            "currency": "EUR"
          }
        ]
      }
    ],
    "erp_name": [
      {
        "locale": "en_US",
        "scope": null,
        "data": "Amor"
      }
    ],
    "supplier": [
      {
        "locale": null,
        "scope": null,
        "data": "zaro"
      }
    ],
    "collection": [
      {
        "locale": null,
        "scope": null,
        "data": [
          "summer_2016"
        ]
      }
    ],
    "description": [
      {
        "locale": "en_US",
        "scope": "ecommerce",
        "data": "Heritage jacket navy blue tweed suit with single breasted 2 button. 53% wool, 22% polyester, 18% acrylic, 5% nylon, 1% cotton, 1% viscose. Dry Cleaning uniquement.Le mannequin measuring 1m85 and wears UK size 40, size 50 FR"
      }
    ],
    "wash_temperature": [
      {
        "locale": null,
        "scope": null,
        "data": "800"
      }
    ]
  },
  "created": "2017-09-20T15:37:40+02:00",
  "updated": "2017-09-20T15:37:40+02:00",
  "associations": {
    
  }
}
JSON;

        $standardizedProducts['apollon_blue_xxl'] = <<<JSON
{
   "_links": {
       "self": {
           "href": "http://localhost/api/rest/v1/products/apollon_blue_xxl"
       }
   },
  "identifier": "apollon_blue_xxl",
  "family": "clothing",
  "parent": "amor",
  "groups": [
    
  ],
  "variant_group": null,
  "categories": [
    "master_women_blouses_deals"
  ],
  "enabled": true,
  "values": {
    "name": [
      {
        "locale": "en_US",
        "scope": null,
        "data": "Heritage jacket navy"
      }
    ],
    "price": [
      {
        "locale": null,
        "scope": null,
        "data": [
          {
            "amount": "999.00",
            "currency": "EUR"
          }
        ]
      }
    ],
    "erp_name": [
      {
        "locale": "en_US",
        "scope": null,
        "data": "Amor"
      }
    ],
    "supplier": [
      {
        "locale": null,
        "scope": null,
        "data": "zaro"
      }
    ],
    "collection": [
      {
        "locale": null,
        "scope": null,
        "data": [
          "summer_2016"
        ]
      }
    ],
    "description": [
      {
        "locale": "en_US",
        "scope": "ecommerce",
        "data": "Heritage jacket navy blue tweed suit with single breasted 2 button. 53% wool, 22% polyester, 18% acrylic, 5% nylon, 1% cotton, 1% viscose. Dry Cleaning uniquement.Le mannequin measuring 1m85 and wears UK size 40, size 50 FR"
      }
    ],
    "wash_temperature": [
      {
        "locale": null,
        "scope": null,
        "data": "800"
      }
    ]
  },
  "created": "2017-09-20T15:37:40+02:00",
  "updated": "2017-09-20T15:37:40+02:00",
  "associations": {
    
  }
}
JSON;

        $standardizedProducts['apollon_blue_xs'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/products/apollon_blue_xs"
        }
    },
    "identifier": "apollon_blue_xs",
      "family": "clothing",
      "parent": "amor",
      "groups": [
        
      ],
      "variant_group": null,
      "categories": [
        
      ],
      "enabled": true,
      "values": {
        "size": [
          {
            "locale": null,
            "scope": null,
            "data": "xs"
          }
        ],
        "name": [
          {
            "locale": "en_US",
            "scope": null,
            "data": "Heritage jacket navy"
          }
        ],
        "price": [
          {
            "locale": null,
            "scope": null,
            "data": [
              {
                "amount": "999.00",
                "currency": "EUR"
              }
            ]
          }
        ],
        "erp_name": [
          {
            "locale": "en_US",
            "scope": null,
            "data": "Amor"
          }
        ],
        "supplier": [
          {
            "locale": null,
            "scope": null,
            "data": "zaro"
          }
        ],
        "collection": [
          {
            "locale": null,
            "scope": null,
            "data": [
              "summer_2016"
            ]
          }
        ],
        "description": [
          {
            "locale": "en_US",
            "scope": "ecommerce",
            "data": "Heritage jacket navy blue tweed suit with single breasted 2 button. 53% wool, 22% polyester, 18% acrylic, 5% nylon, 1% cotton, 1% viscose. Dry Cleaning uniquement.Le mannequin measuring 1m85 and wears UK size 40, size 50 FR"
          }
        ],
        "wash_temperature": [
          {
            "locale": null,
            "scope": null,
            "data": "800"
          }
        ]
      },
      "created": "2017-09-20T15:37:40+02:00",
      "updated": "2017-09-20T15:37:40+02:00",
      "associations": {
        
      }
    }
JSON;

        return $standardizedProducts;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return new Configuration([Configuration::getFunctionalCatalogPath('catalog_modeling')]);
    }
}
