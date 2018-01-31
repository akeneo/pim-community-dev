<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\ProductModel;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Pim\Component\Catalog\tests\integration\Normalizer\NormalizedProductCleaner;
use Symfony\Component\HttpFoundation\Response;

class GetProductModelIntegration extends ApiTestCase
{
    public function testSuccessfullyGetProductModel()
    {
        $standardProductModel = [
            'code' => 'model-biker-jacket-leather',
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
                'price' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => [
                            [
                                'amount' => null,
                                'currency' => 'EUR',
                            ]
                        ]
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
            'updated' => '2017-10-02T15:03:55+02:00'
        ];
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/product-models/model-biker-jacket-leather');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertResponse($response, $standardProductModel);
    }

    public function testFailToGetANonExistingProductModel()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/product-models/model-bayqueur-jaquette');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
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

        $this->assertSame($expected, $result);
    }
}
