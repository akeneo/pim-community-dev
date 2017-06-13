<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\Product;

use Symfony\Component\HttpFoundation\Response;

/**
 * +----------+-------------------------------+-----------------------------------+-----------------------------------+
 * |          |          Categories           |             Locales               |         Attribute groups          |
 * +  Roles   +-------------------------------+-----------------------------------+-----------------------------------+
 * |          |   categoryA2  |   categoryB   |   en_US   |   fr_FR   |   de_DE   | attributeGroupA | attributeGroupB |
 * +==========+===============================+===================================+===================================+
 * | Redactor |      View     |     -         | View,Edit |    View   |     -     |        -        |      View       |
 * | Manager  | View,Edit,Own | View,Edit,Own | View,Edit | View,Edit | View,Edit |    View,Edit    |    View,Edit    |
 * +================+===============================+===================================+=============================+
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ListProductWithPermissionsIntegration extends AbstractProductTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->createProduct('product_viewable_by_everybody_1', [
            'categories' => ['categoryA2'],
            'values'     => [
                'a_localized_and_scopable_text_area' => [
                    ['data' => 'EN ecommerce', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                    ['data' => 'FR ecommerce', 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
                    ['data' => 'DE ecommerce', 'locale' => 'de_DE', 'scope' => 'ecommerce']
                ],
                'a_number_float' => [['data' => '12.05', 'locale' => null, 'scope' => null]],
                'a_localizable_image' => [
                    ['data' => $this->getFixturePath('akeneo.jpg'), 'locale' => 'en_US', 'scope' => null],
                    ['data' => $this->getFixturePath('akeneo.jpg'), 'locale' => 'fr_FR', 'scope' => null],
                    ['data' => $this->getFixturePath('akeneo.jpg'), 'locale' => 'de_DE', 'scope' => null]
                ],
            ]
        ]);

        $this->createProduct('product_viewable_by_everybody_2', [
            'categories' => ['categoryA2', 'categoryB']
        ]);

        $this->createProduct('product_not_viewable_by_redactor', [
            'categories' => ['categoryB']
        ]);

        $this->createProduct('product_without_category', [
            'associations' => [
                'X_SELL' => ['products' => ['product_viewable_by_everybody_2', 'product_not_viewable_by_redactor']]
            ]
        ]);
    }

    public function testProductViewableByManager()
    {
        $standardizedProducts = $this->getStandardizedProducts();
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');

        $client->request('GET', 'api/rest/v1/products');
        $expected = <<<JSON
{
    "_links": {
        "self": {"href": "http://localhost/api/rest/v1/products?limit=10"},
        "first": {"href": "http://localhost/api/rest/v1/products?limit=10"}
    },
    "current_page": null,
    "_embedded": {
        "items": [
            {$standardizedProducts['product_viewable_by_everybody_1']},
            {$standardizedProducts['product_viewable_by_everybody_2']},
            {$standardizedProducts['product_not_viewable_by_redactor']},
            {$standardizedProducts['product_without_category']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testProductViewableByRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/products');
        $expected = <<<JSON
{
    "_links": {
        "self": {"href": "http://localhost/api/rest/v1/products?limit=10"},
        "first": {"href": "http://localhost/api/rest/v1/products?limit=10"}
    },
    "current_page": null,
    "_embedded": {
        "items": [
            {
                "_links": {
                    "self": {
                        "href": "http://localhost/api/rest/v1/products/product_viewable_by_everybody_1"
                    }
                },
                "identifier": "product_viewable_by_everybody_1",
                "family": null,
                "groups": [],
                "variant_group": null,
                "categories": ["categoryA2"],
                "enabled": true,
                "values": {
                    "a_number_float": [
                        {
                            "data": "12.05",
                            "locale": null,
                            "scope": null
                        }
                    ],
                    "a_localizable_image": [
                        {
                            "data": "3/3/6/a/336af1d213f9953530b3a7c4b4aeaf57615dbaaf_akeneo.jpg",
                            "locale": "en_US",
                            "scope": null,
                            "_links": {
                                "download": {
                                    "href": "http://localhost/api/rest/v1/media-files/3/3/6/a/336af1d213f9953530b3a7c4b4aeaf57615dbaaf_akeneo.jpg"
                                }
                            }
                        },
                        {
                            "data": "3/3/6/a/336af1d213f9953530b3a7c4b4aeaf57615dbaaf_akeneo.jpg",
                            "locale": "fr_FR",
                            "scope": null,
                            "_links": {
                                "download": {
                                    "href": "http://localhost/api/rest/v1/media-files/3/3/6/a/336af1d213f9953530b3a7c4b4aeaf57615dbaaf_akeneo.jpg"
                                }
                            }
                        }
                    ]
                },
                "created": "2017-03-11T10:39:38+01:00",
                "updated": "2017-03-11T10:39:38+01:00",
                "associations": {}
            },
            {
                "_links": {
                    "self": {
                        "href": "http://localhost/api/rest/v1/products/product_viewable_by_everybody_2"
                    }
                },
                "identifier": "product_viewable_by_everybody_2",
                "family": null,
                "groups": [],
                "variant_group": null,
                "categories": ["categoryA2"],
                "enabled": true,
                "values": {},
                "created": "2017-03-11T10:39:38+01:00",
                "updated": "2017-03-11T10:39:38+01:00",
                "associations": {}
            },
            {
                "_links": {
                    "self": {
                        "href": "http://localhost/api/rest/v1/products/product_without_category"
                    }
                },
                "identifier": "product_without_category",
                "family": null,
                "groups": [],
                "variant_group": null,
                "categories": [],
                "enabled": true,
                "values": {},
                "created": "2017-03-11T10:39:38+01:00",
                "updated": "2017-03-11T10:39:38+01:00",
                "associations": {
                    "X_SELL": {
                        "products": ["product_viewable_by_everybody_2"],
                        "groups": []
                    }
                }
            }
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    /**
     * @return array
     */
    private function getStandardizedProducts()
    {
        $standardizedProducts['product_viewable_by_everybody_1'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/products/product_viewable_by_everybody_1"
        }
    },
    "identifier": "product_viewable_by_everybody_1",
    "family": null,
    "groups": [],
    "variant_group": null,
    "categories": ["categoryA2"],
    "enabled": true,
    "values": {
        "a_localized_and_scopable_text_area": [
            {
                "data": "DE ecommerce",
                "locale": "de_DE",
                "scope": "ecommerce"
            },
            {
                "data": "EN ecommerce",
                "locale": "en_US",
                "scope": "ecommerce"
            },
            {
                "data": "FR ecommerce",
                "locale": "fr_FR",
                "scope": "ecommerce"
            }
        ],
        "a_number_float": [
            {
                "data": "12.05",
                "locale": null,
                "scope": null
            }
        ],
        "a_localizable_image": [
            {
                "data": "3/3/6/a/336af1d213f9953530b3a7c4b4aeaf57615dbaaf_akeneo.jpg",
                "locale": "de_DE",
                "scope": null,
                "_links": {
                    "download": {
                        "href": "http://localhost/api/rest/v1/media-files/3/3/6/a/336af1d213f9953530b3a7c4b4aeaf57615dbaaf_akeneo.jpg"
                    }
                }
            },
            {
                "data": "3/3/6/a/336af1d213f9953530b3a7c4b4aeaf57615dbaaf_akeneo.jpg",
                "locale": "en_US",
                "scope": null,
                "_links": {
                    "download": {
                        "href": "http://localhost/api/rest/v1/media-files/3/3/6/a/336af1d213f9953530b3a7c4b4aeaf57615dbaaf_akeneo.jpg"
                    }
                }
            },
            {
                "data": "3/3/6/a/336af1d213f9953530b3a7c4b4aeaf57615dbaaf_akeneo.jpg",
                "locale": "fr_FR",
                "scope": null,
                "_links": {
                    "download": {
                        "href": "http://localhost/api/rest/v1/media-files/3/3/6/a/336af1d213f9953530b3a7c4b4aeaf57615dbaaf_akeneo.jpg"
                    }
                }
            }
        ]
    },
    "created": "2017-03-11T10:39:38+01:00",
    "updated": "2017-03-11T10:39:38+01:00",
    "associations": {}
}
JSON;

        $standardizedProducts['product_viewable_by_everybody_2'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/products/product_viewable_by_everybody_2"
        }
    },
    "identifier": "product_viewable_by_everybody_2",
    "family": null,
    "groups": [],
    "variant_group": null,
    "categories": ["categoryA2","categoryB"],
    "enabled": true,
    "values": {},
    "created": "2017-03-11T10:39:38+01:00",
    "updated": "2017-03-11T10:39:38+01:00",
    "associations": {}
}
JSON;

        $standardizedProducts['product_not_viewable_by_redactor'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/products/product_not_viewable_by_redactor"
        }
    },
    "identifier": "product_not_viewable_by_redactor",
    "family": null,
    "groups": [],
    "variant_group": null,
    "categories": ["categoryB"],
    "enabled": true,
    "values": {},
    "created": "2017-03-11T10:39:38+01:00",
    "updated": "2017-03-11T10:39:38+01:00",
    "associations": {}
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
    "groups": [],
    "variant_group": null,
    "categories": [],
    "enabled": true,
    "values": {},
    "created": "2017-03-11T10:39:38+01:00",
    "updated": "2017-03-11T10:39:38+01:00",
    "associations": {
        "X_SELL": {
            "products": ["product_viewable_by_everybody_2", "product_not_viewable_by_redactor"],
            "groups": []
        }
    }
}
JSON;

        return $standardizedProducts;
    }
}
