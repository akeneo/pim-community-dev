<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\ProductModel\ExternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi\AbstractProductTestCase;
use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedProductCleaner;

class AbstractProductModelTestCase extends AbstractProductTestCase
{
    /** @var string[] */
    protected $productModelCodes;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->productModelCodes = ['sweat', 'shoes', 'tshirt', 'trousers', 'hat', 'handbag'];

        foreach ($this->productModelCodes as $item) {
            $this->createProductModel(
                [
                    'code' => $item,
                    'family_variant' => 'familyVariantA1',
                    'values'  => [
                        'a_price'  => [
                            'data' => [
                                'data' => [['amount' => '50', 'currency' => 'EUR']],
                                'locale' => null,
                                'scope' => null
                            ],
                        ],
                        'a_number_float'  => [['data' => '12.5', 'locale' => null, 'scope' => null]],
                        'a_localized_and_scopable_text_area'  => [
                            ['data' => sprintf('I like %s!', $item), 'locale' => 'en_US', 'scope' => 'ecommerce']
                        ],
                    ]
                ]
            );
        }
    }

    /**
     * Return product models as JSON standard format
     *
     * @return array
     */
    protected function getStandardizedProductModels()
    {
        $standardizedProductModels = [];

        foreach ($this->productModelCodes as $item) {
            $standardizedProductModels[$item] = <<<JSON
{
    "_links":{
        "self":{
            "href":"http:\/\/localhost\/api\/rest\/v1\/product-models\/$item"
        }
    },
    "code":"$item",
    "family": "familyA",
    "family_variant":"familyVariantA1",
    "parent":null,
    "categories":[

    ],
    "values":{
        "a_price":[
            {
                "locale":null,
                "scope":null,
                "data":[
                    {
                        "amount":"50.00",
                        "currency":"EUR"
                    }
                ]
            }
        ],
        "a_number_float":[
            {
                "locale":null,
                "scope":null,
                "data":"12.5000"
            }
        ],
        "a_localized_and_scopable_text_area":[
            {
                "locale":"en_US",
                "scope":"ecommerce",
                "data":"I like $item!"
            }
        ]
    },
    "created":"2017-10-04T18:04:10+02:00",
    "updated":"2017-10-04T18:04:10+02:00",
    "associations": {
        "PACK": {
            "products": [],
            "product_models": [],
            "groups": []
        },
        "SUBSTITUTION": {
            "products": [],
            "product_models": [],
            "groups": []
        },
        "UPSELL": {
            "products": [],
            "product_models": [],
            "groups": []
        },
        "X_SELL": {
            "products": [],
            "product_models": [],
            "groups": []
        }
    },
    "quantified_associations": []
}
JSON;
        }

        return $standardizedProductModels;
    }

    /**
     * @param array $expectedProductModel normalized data of the product model that should be created
     * @param string $code code of the product model that should be created
     */
    protected function assertSameProductModels(array $expectedProductModel, $code)
    {
        $this->getOrmCacheClearer()->clear();
        $productModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier($code);
        $standardizedProductModel = $this->get('pim_standard_format_serializer')->normalize($productModel, 'standard');

        NormalizedProductCleaner::clean($expectedProductModel);
        NormalizedProductCleaner::clean($standardizedProductModel);

        $this->assertSame($expectedProductModel, $standardizedProductModel);
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getOrmCacheClearer(): EntityManagerClearerInterface
    {
        return $this->get('pim_connector.doctrine.cache_clearer');
    }
}
