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

        $client->request('GET', 'api/rest/v1/products/apollon_blue_xl');

        $standardVariantProduct = [
            "identifier"    => "apollon_blue_xl",
            "family"        => "clothing",
            "parent"        => "apollon_blue",
            "groups"        => [],
            "variant_group" => null,
            "categories"    => [],
            "enabled"       => true,
            "values"        => [
                "size"             => [
                    [
                        "locale" => null,
                        "scope"  => null,
                        "data"   => "xl",
                    ],
                ],
                "color"            => [
                    [
                        "locale" => null,
                        "scope"  => null,
                        "data"   => "blue",
                    ],
                ],
                "variation_name"   => [
                    [
                        "locale" => "en_US",
                        "scope"  => null,
                        "data"   => "Apollon blue",
                    ],
                ],
                "name"             => [
                    [
                        "locale" => "en_US",
                        "scope"  => null,
                        "data"   => "Long gray suit jacket and matching pants unstructured",
                    ],
                ],
                "price"            => [
                    [
                        "locale" => null,
                        "scope"  => null,
                        "data"   => [
                            [
                                "amount"   => "899.00",
                                "currency" => "EUR",
                            ],
                        ],
                    ],
                ],
                "erp_name"         => [
                    [
                        "locale" => "en_US",
                        "scope"  => null,
                        "data"   => "Apollon",
                    ],
                ],
                "supplier"         => [
                    [
                        "locale" => null,
                        "scope"  => null,
                        "data"   => "zaro",
                    ],
                ],
                "collection"       => [
                    [
                        "locale" => null,
                        "scope"  => null,
                        "data"   => [
                            "winter_2016",
                        ],
                    ],
                ],
                "description"      => [
                    [
                        "locale" => "en_US",
                        "scope"  => "ecommerce",
                        "data"   => "Long gray suit jacket and matching pants unstructured. 61% wool, 30% polyester, 9% ramie. Dry clean only.",
                    ],
                ],
                "wash_temperature" => [
                    [
                        "locale" => null,
                        "scope"  => null,
                        "data"   => "600",
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
        return new Configuration([Configuration::getFunctionalCatalogPath('catalog_modeling')]);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $product = $this->get('pim_catalog.builder.variant_product')->createProduct('apollon_blue_xl', 'clothing');
        $this->get('pim_catalog.updater.product')->update($product, [
            'parent' => 'apollon_blue',
            'values' => [
                'size' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'xl',
                    ],
                ],
            ],
        ]);

        $errors = $this->get('pim_catalog.validator.product')->validate($product);
        if (0 !== $errors->count()) {
            throw new \Exception(sprintf(
                                     'Impossible to setup test in %s: %s',
                                     static::class,
                                     $errors->get(0)->getMessage()
                                 ));
        }

        $this->get('pim_catalog.saver.product')->save($product);
        $this->get('pim_catalog.validator.unique_value_set')->reset();
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
