<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\Product;

/**
 * +----------+-------------------------------+-----------------------------------+-----------------------------------------------------+
 * |          |          Categories           |             Locales               |                  Attribute groups                   |
 * +  Roles   +-------------------------------+-----------------------------------+-----------------------------------+-----------------+
 * |          |   categoryA2  |   categoryB   |   en_US   |   fr_FR   |   de_DE   | attributeGroupA | attributeGroupB | attributeGroupC |
 * +----------+-------------------------------+-----------------------------------+-----------------------------------------------------+
 * | Redactor |      View     |     -         | View,Edit |    View   |     -     |    View,Edit    |      View       |        -        |
 * | Manager  | View,Edit,Own | View,Edit,Own | View,Edit | View,Edit | View,Edit |    View,Edit    |    View,Edit    |    View,Edit    |
 * +----------+-------------------------------+-----------------------------------+-----------------------------------------------------+
 */
class ListProductWithPermissionsIntegration extends AbstractProductTestCase
{
    public function testProductsViewableByManager()
    {
        $standardizedProducts = $this->getStandardizedProducts();
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');

        $client->request('GET', 'api/rest/v1/products');
        $expected = <<<JSON
{
    "_links": {
        "self": {"href": "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=10"},
        "first": {"href": "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=10"}
    },
    "current_page": 1,
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

    public function testProductsViewableByRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/products');
        $expected = <<<JSON
{
    "_links": {
        "self": {"href": "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=10"},
        "first": {"href": "http://localhost/api/rest/v1/products?page=1&with_count=false&pagination_type=page&limit=10"}
    },
    "current_page": 1   ,
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
                "parent": null,
                "groups": [],
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
                    ],
                    "a_localized_and_scopable_text_area": [
                        { "data": "EN ecommerce", "locale": "en_US", "scope": "ecommerce" },
                        { "data": "FR ecommerce", "locale": "fr_FR", "scope": "ecommerce" }
                    ]
                },
                "created": "2017-03-11T10:39:38+01:00",
                "updated": "2017-03-11T10:39:38+01:00",
                "associations": {},
                "metadata": {
                    "workflow_status": "read_only"
                }
            },
            {
                "_links": {
                    "self": {
                        "href": "http://localhost/api/rest/v1/products/product_viewable_by_everybody_2"
                    }
                },
                "identifier": "product_viewable_by_everybody_2",
                "family": null,
                "parent": null,
                "groups": [],
                "categories": ["categoryA2"],
                "enabled": true,
                "values": {},
                "created": "2017-03-11T10:39:38+01:00",
                "updated": "2017-03-11T10:39:38+01:00",
                "associations": {},
                "metadata": {
                    "workflow_status": "read_only"
                }
            },
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
                "values": {},
                "created": "2017-03-11T10:39:38+01:00",
                "updated": "2017-03-11T10:39:38+01:00",
                "associations": {
                    "X_SELL": {
                        "products": ["product_viewable_by_everybody_2"],
                        "groups": [],
                        "product_models": []
                    },
                    "PACK": {
                        "products": [],
                        "groups": [],
                        "product_models": []
                    },
                    "UPSELL": {
                        "products": [],
                        "groups": [],
                        "product_models": []
                    },
                    "SUBSTITUTION": {
                        "products": [],
                        "groups": [],
                        "product_models": []
                    }
                },
                "metadata": {
                    "workflow_status": "working_copy"
                }
            }
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }
}
