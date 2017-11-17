<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\Product;

use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\tests\integration\Normalizer\NormalizedProductCleaner;
use PimEnterprise\Component\Workflow\Model\ProductDraftInterface;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractProductTestCase extends ApiTestCase
{
    /**
     * {@inheritdoc}
     */
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
                'a_metric_without_decimal_negative' => [
                    ['data' => ['amount' => -10, 'unit' => 'CELSIUS'], 'locale' => null, 'scope' => null]
                ],
                'a_multi_select' => [
                    ['locale' => null, 'scope' => null, 'data' => ['optionA', 'optionB']],
                ]
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

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @param string $identifier
     * @param array  $data
     *
     * @return ProductInterface
     */
    protected function createProduct($identifier, array $data = [])
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $this->get('pim_catalog.saver.product')->save($product);

        $this->get('akeneo_elasticsearch.client.product')->refreshIndex();

        return $product;
    }

    /**
     * @param string           $userName
     * @param ProductInterface $product
     * @param array            $changes
     *
     * @return ProductDraftInterface
     */
    protected function createProductDraft(
        string $userName,
        ProductInterface $product,
        array $changes
    ) : ProductDraftInterface {
        $this->get('pim_catalog.updater.product')->update($product, $changes);

        $productDraft = $this->get('pimee_workflow.builder.draft')->build($product, $userName);
        $this->get('pimee_workflow.saver.product_draft')->save($productDraft);

        return $productDraft;
    }

    /**
     * @param Response $response
     * @param string   $expected
     */
    protected function assertListResponse(Response $response, $expected)
    {
        $result = json_decode($response->getContent(), true);
        $expected = json_decode($expected, true);

        if (!isset($result['_embedded'])) {
            \PHPUnit_Framework_Assert::fail($response->getContent());
        }

        foreach ($result['_embedded']['items'] as $index => $product) {
            NormalizedProductCleaner::clean($result['_embedded']['items'][$index]);

            if (isset($expected['_embedded']['items'][$index])) {
                NormalizedProductCleaner::clean($expected['_embedded']['items'][$index]);
            }
        }

        $this->assertEquals($expected, $result);
    }

    /**
     * @param string $sql
     *
     * @return array
     */
    protected function getDatabaseData(string $sql): array
    {
        $stmt = $this->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Warning, the values of the key "workflow_status" can change according to the user permissions.
     * They are currently set according to an user having "own" permission on every product.
     *
     * @return array
     */
    protected function getStandardizedProducts()
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
    "parent": null,
    "groups": [],
    "categories": ["categoryA2"],
    "enabled": true,
    "values": {
        "a_localized_and_scopable_text_area": [
            { "data": "DE ecommerce", "locale": "de_DE", "scope": "ecommerce" },
            { "data": "EN ecommerce", "locale": "en_US", "scope": "ecommerce" },
            { "data": "FR ecommerce", "locale": "fr_FR", "scope": "ecommerce" }
        ],
        "a_multi_select": [
            {"locale":null,"scope":null,"data":["optionA","optionB"]}
        ],
        "a_number_float": [
            { "data": "12.05", "locale": null, "scope": null }
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
        ],
        "a_metric_without_decimal_negative": [
            { "data": {"amount": -10, "unit": "CELSIUS"}, "locale": null, "scope": null }
        ]
    },
    "created": "2017-03-11T10:39:38+01:00",
    "updated": "2017-03-11T10:39:38+01:00",
    "associations": {},
    "metadata": {
        "workflow_status": "working_copy"
    }
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
    "parent": null,
    "groups": [],
    "categories": ["categoryA2","categoryB"],
    "enabled": true,
    "values": {},
    "created": "2017-03-11T10:39:38+01:00",
    "updated": "2017-03-11T10:39:38+01:00",
    "associations": {},
    "metadata": {
        "workflow_status": "working_copy"
    }
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
    "parent": null,
    "groups": [],
    "categories": ["categoryB"],
    "enabled": true,
    "values": {},
    "created": "2017-03-11T10:39:38+01:00",
    "updated": "2017-03-11T10:39:38+01:00",
    "associations": {},
    "metadata": {
        "workflow_status": "working_copy"
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
    "values": {},
    "created": "2017-03-11T10:39:38+01:00",
    "updated": "2017-03-11T10:39:38+01:00",
    "associations": {
        "X_SELL": {
            "products": ["product_viewable_by_everybody_2", "product_not_viewable_by_redactor"],
            "groups": []
        },
        "PACK": {
            "products": [],
            "groups": []
        },
        "UPSELL": {
            "products": [],
            "groups": []
        },
        "SUBSTITUTION": {
            "products": [],
            "groups": []
        }
    },
    "metadata": {
        "workflow_status": "working_copy"
    }
}
JSON;

        return $standardizedProducts;
    }

    /**
     * @param Response $response
     * @param string   $message
     * @param string   $documentation
     */
    protected function assertError422(Response $response, $message, $documentation)
    {
        $expected = <<<JSON
{
  "code": 422,
  "message": "{$message}. Check the expected format on the API documentation.",
  "_links": {
    "documentation": {
      "href": "http://api.akeneo.com/api-reference.html#{$documentation}"
    }
  }
}
JSON;

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    /**
     * @param array  $expectedProduct normalized data of the product that should be created
     * @param string $identifier      identifier of the product that should be created
     */
    protected function assertSameProducts(array $expectedProduct, $identifier)
    {
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
        $standardizedProduct = $this->get('pim_serializer')->normalize($product, 'standard');

        NormalizedProductCleaner::clean($standardizedProduct);
        NormalizedProductCleaner::clean($expectedProduct);

        $this->assertSame($expectedProduct, $standardizedProduct);
    }

    /**
     * @param array  $data
     * @param string $identifier
     */
    protected function updateProduct(array $data, $identifier)
    {
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $this->get('pim_catalog.saver.product')->save($product);
        $this->get('akeneo_elasticsearch.client.product')->refreshIndex();
    }
}
