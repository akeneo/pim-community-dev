<?php

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\Product;

use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedProductCleaner;
use AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\PermissionFixturesLoader;
use Symfony\Component\HttpFoundation\Response;

/**
 * +----------+-----------------------------------------------+
 * |          |                  Categories                   |
 * +  Roles   +-----------------------------------------------+
 * |          |   categoryA2  |   categoryA   |   categoryB   |
 * +----------+-----------------------------------------------+
 * | Redactor |      View     |   View,Edit   |       -       |
 * | Manager  | View,Edit,Own | View,Edit,Own | View,Edit,Own |
 * +----------+-----------------------------------------------+
 *
 * +----------+-----------------------------------+
 * |          |             Locales               |
 * +  Roles   +-----------------------------------+
 * |          |   en_US   |   fr_FR   |   de_DE   |
 * +----------+-----------------------------------+
 * | Redactor | View,Edit |    View   |     -     |
 * | Manager  | View,Edit | View,Edit | View,Edit |
 * +----------+-----------------------------------+
 *
 * +----------+-----------------------------------------------------+
 * |          |                  Attribute groups                   |
 * +  Roles   +-----------------------------------+-----------------+
 * |          | attributeGroupA | attributeGroupB | attributeGroupC |
 * +----------+-----------------------------------------------------+
 * | Redactor |    View,Edit    |      View       |        -        |
 * | Manager  |    View,Edit    |    View,Edit    |    View,Edit    |
 * +----------+-----------------------------------------------------+
 */
class GetProductWithPermissionsEndToEnd extends AbstractProductTestCase
{
    /** @var PermissionFixturesLoader */
    private $loader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loader = new PermissionFixturesLoader($this->testKernel->getContainer());
    }

    public function testProductViewableByManager()
    {
        $standardizedProducts = $this->getStandardizedProducts();
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');

        $client->request('GET', 'api/rest/v1/products/product_viewable_by_everybody_1');

        $this->assertResponse($client->getResponse(), $standardizedProducts['product_viewable_by_everybody_1']);
    }

    public function testProductViewableByRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/products/product_viewable_by_everybody_1');
        $expected = <<<JSON
{
    "identifier": "product_viewable_by_everybody_1",
    "family": null,
    "parent": null,
    "groups": [],
    "categories": ["categoryA2"],
    "enabled": true,
    "values": {
        "a_number_float": [
            {
                "data": "12.0500",
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
}
JSON;

        $this->assertResponse($client->getResponse(), $expected);
    }

    public function testNotAccessibleProductForRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/products/product_not_viewable_by_redactor');
        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    public function testToReturnProductEvenIfADraftExists()
    {
        $product = $this->createProduct('product_draft_for_redactor', [
            'categories' => ['categoryA'],
            'values' => [
                'a_yes_no' => [['data' => false, 'locale' => null, 'scope' => null]]
            ]
        ]);
        $this->createEntityWithValuesDraft('mary', $product, [
            'values' => [
                'a_text_area' => [['data' => 'a text area', 'locale' => null, 'scope' => null]],
                'a_yes_no' => [['data' => true, 'locale' => null, 'scope' => null]]
            ]
        ]);

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/products/product_draft_for_redactor');

        $expected = <<<JSON
{
    "identifier":"product_draft_for_redactor",
    "family":null,
    "parent":null,
    "groups":[],
    "categories":["categoryA"],
    "enabled":true,
    "values":{
        "a_yes_no":[
            {"locale":null,"scope":null,"data":false}
        ]
    },
    "created":"2017-09-25T20:20:20+02:00",
    "updated":"2017-09-25T20:20:20+02:00",
    "associations":{},
    "metadata":{
        "workflow_status":"draft_in_progress"
    }
}
JSON;

        $this->assertResponse($client->getResponse(), $expected);
    }

    public function testGetProductHavingAssociations()
    {
        $this->loader->loadProductsForAssociationPermissions();

        $this->createProduct('product_having_associations', [
            'categories' => ['categoryA'],
            'associations' => [
                'X_SELL' => [
                    'products' => [
                        'product_view',
                        'product_no_view',
                    ],
                    'product_models' => [
                        'product_model_view',
                        'product_model_no_view',
                    ],
                ],
            ],
        ]);

        $expectedResponse = <<<JSON
{
    "identifier": "product_having_associations",
    "family": null,
    "parent": null,
    "groups": [],
    "categories": [
        "categoryA"
    ],
    "enabled": true,
    "values": {},
    "created": "2018-03-05T13:11:04+00:00",
    "updated": "2018-03-05T13:11:04+00:00",
    "associations": {
        "PACK": {
            "groups": [],
            "products": [],
            "product_models": []
        },
        "SUBSTITUTION": {
            "groups": [],
            "products": [],
            "product_models": []
        },
        "UPSELL": {
            "groups": [],
            "products": [],
            "product_models": []
        },
        "X_SELL": {
            "groups": [],
            "products": [
                "product_view"
            ],
            "product_models": [
                "product_model_view"
            ]
        }
    },
    "metadata": {
        "workflow_status": "working_copy"
    }
}
JSON;

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('GET', 'api/rest/v1/products/product_having_associations');

        $this->assertResponse($client->getResponse(), $expectedResponse);
    }

    /**
     * @param Response $response
     * @param string   $expected
     */
    private function assertResponse(Response $response, $expected)
    {
        $result = json_decode($response->getContent(), true);
        $expected = json_decode($expected, true);
        unset($expected['_links']);

        NormalizedProductCleaner::clean($expected);
        NormalizedProductCleaner::clean($result);
        
        $this->assertJsonStringEqualsJsonString(json_encode($expected), json_encode($result));
    }
}
