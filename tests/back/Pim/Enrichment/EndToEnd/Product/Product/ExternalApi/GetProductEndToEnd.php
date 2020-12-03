<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi;

use Akeneo\Test\Integration\Configuration;
use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedProductCleaner;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group ce
 */
class GetProductEndToEnd extends AbstractProductTestCase
{

    public function test_it_gets_a_product_with_attribute_options_simple_select()
    {
        $this->createProduct('product', [
            'family'     => 'familyA',
            'categories' => [],
            'groups'     => [],
            'values'     => [
                'a_simple_select' => [
                    ['data' => 'optionA', 'locale' => null, 'scope' => null]
                ],
            ],
        ]);

        $client = $this->createAuthenticatedClient();
        $client->request('GET', 'api/rest/v1/products/product?with_attribute_options=true');

        $expectedProduct = [
            'identifier'    => 'product',
            'family'        => 'familyA',
            'parent'        => null,
            'groups'        => [],
            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                'a_simple_select' => [
                    [
                        'data' => 'optionA',
                        'locale' => null,
                        'scope' => null,
                        'linked_data' => [
                            'attribute' => 'a_simple_select',
                            'code' => 'optionA',
                            'labels' => [
                                'en_US' => 'Option A',
                            ],
                        ],
                    ],
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [
                'PACK'   => ['groups' => [], 'products' => [], 'product_models' => []],
                'SUBSTITUTION' => ['groups' => [], 'products' => [], 'product_models' => []],
                'UPSELL' => ['groups' => [], 'products' => [], 'product_models' => []],
                'X_SELL' => ['groups' => [], 'products' => [], 'product_models' => []],
            ],
            'quantified_associations' => [],
        ];

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertResponse($response, $expectedProduct);
    }

    public function test_it_gets_a_product_with_attribute_options_multi_select()
    {
        $this->createProduct('product', [
            'family'     => 'familyA',
            'categories' => [],
            'groups'     => [],
            'values'     => [
                'a_multi_select' => [
                    ['data' => ['optionA','optionB'], 'locale' => null, 'scope' => null]
                ],
            ],
        ]);

        $client = $this->createAuthenticatedClient();
        $client->request('GET', 'api/rest/v1/products/product?with_attribute_options=true');

        $expectedProduct = [
            'identifier'    => 'product',
            'family'        => 'familyA',
            'parent'        => null,
            'groups'        => [],
            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                'a_multi_select' => [
                    [
                        'data' => ['optionA', 'optionB'],
                        'locale' => null,
                        'scope' => null,
                        'linked_data' => [
                            'optionA' => [
                                'attribute' => 'a_multi_select',
                                'code' => 'optionA',
                                'labels' => [
                                    'en_US' => 'Option A',
                                ],
                            ],
                            'optionB' => [
                                'attribute' => 'a_multi_select',
                                'code' => 'optionB',
                                'labels' => [
                                    'en_US' => 'Option B',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [
                'PACK'   => ['groups' => [], 'products' => [], 'product_models' => []],
                'SUBSTITUTION' => ['groups' => [], 'products' => [], 'product_models' => []],
                'UPSELL' => ['groups' => [], 'products' => [], 'product_models' => []],
                'X_SELL' => ['groups' => [], 'products' => [], 'product_models' => []],
            ],
            'quantified_associations' => [],
        ];

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertResponse($response, $expectedProduct);
    }

    /**
     * @group critical
     */
    public function test_it_gets_a_product()
    {
        $this->createProduct('product', [
            'family'     => 'familyA1',
            'enabled'       => true,
            'categories' => ['categoryA', 'master', 'master_china'],
            'groups'     => ['groupA', 'groupB'],
            'values'     => [
                'a_date' => [
                    ['data' => '2016-06-28', 'locale' => null, 'scope' => null]
                ],
            ],
        ]);

        $client = $this->createAuthenticatedClient();
        $client->request('GET', 'api/rest/v1/products/product');

        $standardProduct = [
            'identifier'    => 'product',
            'family'        => 'familyA1',
            'parent'        => null,
            'groups'        => ['groupA', 'groupB'],
            'categories'    => ['categoryA', 'master', 'master_china'],
            'enabled'       => true,
            'values'        => [
                'a_date' => [
                    ['data' => '2016-06-28T00:00:00+02:00', 'locale' => null, 'scope' => null]
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [
                'PACK'   => ['groups' => [], 'products' => [], 'product_models' => []],
                'SUBSTITUTION' => ['groups' => [], 'products' => [], 'product_models' => []],
                'UPSELL' => ['groups' => [], 'products' => [], 'product_models' => []],
                'X_SELL' => ['groups' => [], 'products' => [], 'product_models' => []],
            ],
            'quantified_associations' => [],
        ];

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertResponse($response, $standardProduct);
    }

    public function test_it_throws_a_404_response_when_the_product_is_not_found()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products/not_found');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content, 'response contains 2 items');
        $this->assertSame(Response::HTTP_NOT_FOUND, $content['code']);
        $this->assertSame('Product "not_found" does not exist or you do not have permission to access it.', $content['message']);
    }

    public function test_it_gets_a_product_with_quality_scores()
    {
        $this->createProduct('product', [
            'family'     => 'familyA',
            'categories' => [],
            'groups'     => [],
            'values'     => [],
        ]);

        $client = $this->createAuthenticatedClient();
        $client->request('GET', 'api/rest/v1/products/product?with_quality_scores=true');

        $expectedProduct = [
            'identifier'    => 'product',
            'family'        => 'familyA',
            'parent'        => null,
            'groups'        => [],
            'categories'    => [],
            'enabled'       => true,
            'values'        => [],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [
                'PACK'   => ['groups' => [], 'products' => [], 'product_models' => []],
                'SUBSTITUTION' => ['groups' => [], 'products' => [], 'product_models' => []],
                'UPSELL' => ['groups' => [], 'products' => [], 'product_models' => []],
                'X_SELL' => ['groups' => [], 'products' => [], 'product_models' => []],
            ],
            'quantified_associations' => [],
            'quality_scores' => [
                'tablet' => [
                    'de_DE' => 'E',
                    'en_US' => 'E',
                    'fr_FR' => 'E',
                ],
                'ecommerce' => [
                    'en_US' => 'E',
                ],
                'ecommerce_china' => [
                    'en_US' => 'E',
                    'zh_CN' => 'E',
                ],
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertResponse($response, $expectedProduct);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function assertResponse(Response $response, array $expected)
    {
        $result = json_decode($response->getContent(), true);

        NormalizedProductCleaner::clean($expected);
        NormalizedProductCleaner::clean($result);

        $this->assertEquals($expected, $result);
    }
}
