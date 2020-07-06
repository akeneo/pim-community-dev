<?php

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\Product\Update;

use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\EntityWithQuantifiedAssociations\QuantifiedAssociationsTestCaseTrait;
use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedProductCleaner;
use AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\PermissionFixturesLoader;
use AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\Product\AbstractProductTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetQuantifiedAssociationOnProductEndToEnd extends ApiTestCase
{
    use QuantifiedAssociationsTestCaseTrait;

    /** @var PermissionFixturesLoader */
    private $loader;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createQuantifiedAssociationType('PRODUCTSET');
        $this->loader = $this->get('akeneo_integration_tests.loader.permissions');
        $this->loader->loadProductsForQuantifiedAssociationPermissions();
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function testItOnlyReturnAssociatedProductsAndProductModelsGranted()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('GET', 'api/rest/v1/products/product_associated_with_product_and_product_model');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $expectedQuantifiedAssociations = [
            'PRODUCTSET' => [
                'products' => [
                    ['identifier' => 'product_viewable_by_everybody', 'quantity' => 2],
                    ['identifier' => 'product_without_category', 'quantity' => 3],
                ],
                'product_models' => [
                    ['identifier' => 'product_model_viewable_by_everybody', 'quantity' => 5],
                    ['identifier' => 'product_model_without_category', 'quantity' => 6],
                ],
            ],
        ];

        $content = json_decode($response->getContent(), true);
        $this->assertSame($expectedQuantifiedAssociations, $content['quantified_associations']);
    }

    public function testItReturnAllAssociationsWhenUserIsGrantedForAllProductsAndProductModels()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');
        $client->request('GET', 'api/rest/v1/products/product_associated_with_product_and_product_model');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $expectedQuantifiedAssociations = [
            'PRODUCTSET' => [
                'products' => [
                    ['identifier' => 'product_not_viewable_by_redactor', 'quantity' => 1],
                    ['identifier' => 'product_viewable_by_everybody', 'quantity' => 2],
                    ['identifier' => 'product_without_category', 'quantity' => 3],
                ],
                'product_models' => [
                    ['identifier' => 'product_model_not_viewable_by_redactor', 'quantity' => 4],
                    ['identifier' => 'product_model_viewable_by_everybody', 'quantity' => 5],
                    ['identifier' => 'product_model_without_category', 'quantity' => 6],
                ],
            ],
        ];

        $content = json_decode($response->getContent(), true);
        $this->assertSame($expectedQuantifiedAssociations, $content['quantified_associations']);
    }
}
