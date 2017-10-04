<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\Product;

use Akeneo\Test\Integration\Configuration;
use Pim\Component\Catalog\tests\integration\Normalizer\NormalizedProductCleaner;
use Symfony\Component\HttpFoundation\Response;

class GetVariantProductIntegration extends AbstractProductTestCase
{
    public function testGetACompleteVariantProduct()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products/biker-jacket-leather-xxs');

        $standardVariantProduct = [
            "identifier"    => "biker-jacket-leather-xxs",
            "family"        => "clothing",
            "parent"        => "model-biker-jacket-leather",
            "groups"        => [],
            "categories"    => ["master_men_blazers"],
            "enabled"       => true,
            "values"        => [
                "ean"             => [
                    [
                        "locale" => null,
                        "scope"  => null,
                        "data"   => "1234567890362",
                    ],
                ],
                "size" => [
                    [
                        "locale" => null,
                        "scope"  => null,
                        "data"   => "xxs",
                    ],
                ],
                "color"            => [
                    [
                        "locale" => null,
                        "scope"  => null,
                        "data"   => "antique_white",
                    ],
                ],
                "material"   => [
                    [
                        "locale" => null,
                        "scope"  => null,
                        "data"   => "leather",
                    ],
                ],
                "variation_name"   => [
                    [
                        "locale" => "en_US",
                        "scope"  => null,
                        "data"   => "Biker jacket leather",
                    ],
                ],
                "name"             => [
                    [
                        "locale" => "en_US",
                        "scope"  => null,
                        "data"   => "Biker jacket",
                    ],
                ],
                "price"            => [
                    [
                        "locale" => null,
                        "scope"  => null,
                        "data"   => [
                            [
                                "amount"   => null,
                                "currency" => "EUR",
                            ],
                        ],
                    ],
                ],
                "collection"       => [
                    [
                        "locale" => null,
                        "scope"  => null,
                        "data"   => [
                            "summer_2017",
                        ],
                    ],
                ],
                "description"      => [
                    [
                        "locale" => "en_US",
                        "scope"  => "ecommerce",
                        "data"   => "Biker jacket",
                    ],
                ],
            ],
            "created"       => "2017-09-19T15:58:19+02:00",
            "updated"       => "2017-09-19T15:58:19+02:00",
            "associations"  => [],
        ];

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertResponse($response, $standardVariantProduct);
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
