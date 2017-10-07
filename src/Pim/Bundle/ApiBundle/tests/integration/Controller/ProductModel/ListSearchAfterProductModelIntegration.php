<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\ProductModel;

class ListSearchAfterProductModelIntegration extends AbstractProductModelTestCase
{
    public function testSearchAfterPaginationListProductModelsWithoutParameter()
    {
        $standardizedProducts = $this->getStandardizedProductModels();
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/product-models?pagination_type=search_after');
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href": "http://localhost/api/rest/v1/product-models?pagination_type=search_after&limit=10"},
        "first" : {"href": "http://localhost/api/rest/v1/product-models?pagination_type=search_after&limit=10"}
    },
    "_embedded" : {
        "items" : [
            {$standardizedProducts['sweat']},
            {$standardizedProducts['shoes']},
            {$standardizedProducts['tshirt']},
            {$standardizedProducts['trousers']},
            {$standardizedProducts['hat']},
            {$standardizedProducts['handbag']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testSearchAfterPaginationListProductModelsWithNextLink()
    {
        $standardizedProducts = $this->getStandardizedProductModels();
        $client = $this->createAuthenticatedClient();

        $id = [
            'sweat'    => rawurlencode($this->getEncryptedId('sweat')),
            'shoes'    => rawurlencode($this->getEncryptedId('shoes')),
            'trousers' => rawurlencode($this->getEncryptedId('trousers'))
        ];

        $client->request('GET', sprintf('api/rest/v1/product-models?pagination_type=search_after&limit=3&search_after=%s', $id['sweat']));
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href": "http://localhost/api/rest/v1/product-models?pagination_type=search_after&limit=3&search_after={$id['sweat']}"},
        "first" : {"href": "http://localhost/api/rest/v1/product-models?pagination_type=search_after&limit=3"},
        "next"  : {"href": "http://localhost/api/rest/v1/product-models?pagination_type=search_after&limit=3&search_after={$id['trousers']}"}
    },
    "_embedded"    : {
        "items" : [
            {$standardizedProducts['shoes']},
            {$standardizedProducts['tshirt']},
            {$standardizedProducts['trousers']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    public function testSearchAfterPaginationLastPageOfTheListOfProductModels()
    {
        $standardizedProducts = $this->getStandardizedProductModels();
        $client = $this->createAuthenticatedClient();

        $tshirtEncryptedId = rawurlencode($this->getEncryptedId('tshirt'));

        $client->request('GET', sprintf('api/rest/v1/product-models?pagination_type=search_after&limit=4&search_after=%s' , $tshirtEncryptedId));
        $expected = <<<JSON
{
    "_links": {
        "self"  : {"href": "http://localhost/api/rest/v1/product-models?pagination_type=search_after&limit=4&search_after={$tshirtEncryptedId}"},
        "first" : {"href": "http://localhost/api/rest/v1/product-models?pagination_type=search_after&limit=4"}
    },
    "_embedded"    : {
        "items" : [
            {$standardizedProducts['trousers']},
            {$standardizedProducts['hat']},
            {$standardizedProducts['handbag']}
        ]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    /**
     * @param string $productModelIdentifier
     */
    private function getEncryptedId($productModelIdentifier)
    {
        $encrypter = $this->getFromTestContainer('pim_api.security.primary_key_encrypter');
        $repository = $this->getFromTestContainer('pim_catalog.repository.product_model');

        $productModel = $repository->findOneByIdentifier($productModelIdentifier);

        return $encrypter->encrypt($productModel->getId());
    }
}
