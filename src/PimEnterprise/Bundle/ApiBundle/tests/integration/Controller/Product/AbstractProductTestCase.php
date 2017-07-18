<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\Product;

use Akeneo\Test\Integration\Configuration;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\tests\integration\Normalizer\NormalizedProductCleaner;
use PimEnterprise\Bundle\ApiBundle\tests\integration\ApiTestCase;
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
        $rootPath = $this->getParameter('kernel.root_dir') . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;

        return new Configuration(
            [
                Configuration::getTechnicalCatalogPath(),
                $rootPath . 'tests' . DIRECTORY_SEPARATOR . 'catalog' . DIRECTORY_SEPARATOR . 'technical'
            ]
        );
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

        $this->get('akeneo_elasticsearch.client')->refreshIndex();

        return $product;
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
    "groups": [],
    "variant_group": null,
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
