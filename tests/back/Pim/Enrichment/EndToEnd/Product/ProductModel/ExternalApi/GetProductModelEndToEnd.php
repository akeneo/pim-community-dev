<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\ProductModel\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelAssociation;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedProductCleaner;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class GetProductModelEndToEnd extends ApiTestCase
{
    /**
     * @group ce
     * @group critical
     */
    public function testSuccessfullyGetProductModel()
    {
        $this->addAssociationsToProductModel('model-biker-jacket-leather');

        $standardProductModel = [
            'code' => 'model-biker-jacket-leather',
            'family' => 'clothing',
            'family_variant' => 'clothing_material_size',
            'parent' => 'model-biker-jacket',
            'categories' => ['master_men_blazers'],
            'values' => [
                'color' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'antique_white',
                    ]
                ],
                'material' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'leather',
                    ]
                ],
                'variation_name' => [
                    [
                        'locale' => 'en_US',
                        'scope' => null,
                        'data' => 'Biker jacket leather',
                    ]
                ],
                'name' => [
                    [
                        'locale' => 'en_US',
                        'scope' => null,
                        'data' => 'Biker jacket',
                    ]
                ],
                'collection' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => ['summer_2017']
                    ]
                ],
                'description' => [
                    [
                        'locale' => 'en_US',
                        'scope' => 'ecommerce',
                        'data' => 'Biker jacket',
                    ]
                ]
            ],
            'created' => '2017-10-02T15:03:55+02:00',
            'updated' => '2017-10-02T15:03:55+02:00',
            'associations'  => [
                'X_SELL' => ['groups' => [], 'products' => ['biker-jacket-leather-m'], 'product_models' => ['model-biker-jacket-polyester']],
                'PACK' => ['groups' => [], 'products' => [], 'product_models' => []],
                'SUBSTITUTION' => ['groups' => [], 'products' => [], 'product_models' => []],
                'UPSELL' => ['groups' => [], 'products' => [], 'product_models' => []]
            ]
        ];
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/product-models/model-biker-jacket-leather');

        $response = $client->getResponse();
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertResponse($response, $standardProductModel);
    }

    public function testFailToGetANonExistingProductModel()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/product-models/model-bayqueur-jaquette');

        $response = $client->getResponse();
        Assert::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    protected function addAssociationsToProductModel($productModelCode)
    {
        $productModel = $this->get('pim_catalog.repository.product_model')->findOneByCode($productModelCode);

        $association = $productModel->getAssociationForTypeCode('X_SELL');
        if (null === $association) {
            $associationType = $this->get('pim_catalog.repository.association_type')
                ->findOneBy(['code' => 'X_SELL']);

            $association = new ProductModelAssociation();
            $association->setAssociationType($associationType);
            $productModel->addAssociation($association);
        }
        $association->addProductModel(
            $this->get('pim_catalog.repository.product_model')->findOneByCode('model-biker-jacket-polyester')
        );

        $association->addProduct(
            $this->get('pim_catalog.repository.product')->findOneByIdentifier('biker-jacket-leather-m')
        );

        $missingAssociationAdder = $this->get('pim_catalog.association.missing_association_adder');
        $missingAssociationAdder->addMissingAssociations($productModel);

        $errors = $this->get('pim_catalog.validator.product_model')->validate($productModel);

        Assert::assertCount(0, $errors);

        $this->get('pim_catalog.saver.product_model')->save($productModel);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    /**
     * @param Response $response
     * @param array    $expected
     */
    private function assertResponse(Response $response, array $expected)
    {
        $result = json_decode($response->getContent(), true);

        NormalizedProductCleaner::clean($expected);
        NormalizedProductCleaner::clean($result);

        Assert::assertSame($expected, $result);
    }
}
