<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\ProductModel;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\ApiBundle\tests\integration\Controller\Product\AbstractProductTestCase;
use Pim\Component\Catalog\Model\ProductModelInterface;

class LargeAndOrderedListProductModelIntegration extends AbstractProductTestCase
{
    /** @var ProductModelInterface[] */
    private $productModels;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $data = [];
        for ($i = 0; $i < $this->getListSize(); $i++) {
            $data[] = [
                'code' => 'model-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'family_variant' => 'familyVariantA1'
            ];
        }

        foreach ($data as $model) {
            $productModel = $this->createProductModel($model);
            $this->productModels[$productModel->getId()] = $productModel;
        }

        // the API will return products sorted alphabetical by MySQL ID, and that's what we expect
        // for instance, if we have 100 products
        // 1, 10, 100, 11, 12, 13, 14, 15, 16, 17, 18, 19, 2, 20, 21...
        ksort($this->productModels, SORT_STRING);
    }

    public function testPaginationAllProductModels()
    {
        $standardizedProductModels = [];
        foreach ($this->productModels as $productModel) {
            $standardizedProductModels[] = $this->getStandardizedProductModel($productModel->getCode());
        }
        $standardizedProductModels = implode(',', $standardizedProductModels);
        $lastEncryptedId = rawurlencode($this->getEncryptedId(end($this->productModels)));

        $client = $this->createAuthenticatedClient();
        $client->request('GET', 'api/rest/v1/product-models?limit=100&pagination_type=search_after');
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href": "http://localhost/api/rest/v1/product-models?pagination_type=search_after&limit=100"},
        "first" : {"href": "http://localhost/api/rest/v1/product-models?pagination_type=search_after&limit=100"},
        "next" : {"href": "http://localhost/api/rest/v1/product-models?pagination_type=search_after&limit=100&search_after={$lastEncryptedId}"}
    },
    "_embedded"    : {
		"items": [
            {$standardizedProductModels}
		]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @param string $code
     *
     * @return string
     */
    private function getStandardizedProductModel($code)
    {
        $standardized = <<<JSON
{
    "_links":{
        "self":{
            "href":"http:\/\/localhost\/api\/rest\/v1\/product-models\/$code"
        }
    },
    "code":"$code",
    "family_variant":"familyVariantA1",
    "parent":null,
    "categories":[],
    "values":{},
    "created":"2017-10-04T18:04:10+02:00",
    "updated":"2017-10-04T18:04:10+02:00"
}
JSON;

        return $standardized;
    }

    /**
     * @param ProductModelInterface $productModel
     *
     * @return string
     */
    private function getEncryptedId(ProductModelInterface $productModel)
    {
        $encrypter = $this->get('pim_api.security.primary_key_encrypter');

        return $encrypter->encrypt($productModel->getId());
    }

    /**
     * We want to test the API is capable of returning a list of 100 items.
     * (Twice the page of the cursor).
     *
     * @return int
     */
    private function getListSize()
    {
        $cursorPageSize = (int)$this->getParameter('pim_catalog.factory.product_cursor.page_size');

        return $cursorPageSize * 2;
    }
}
