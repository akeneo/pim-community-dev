<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProducts;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\SetGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetDateValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Test\Integration\Configuration;
use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedProductCleaner;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group ce
 */
class GetProductEndToEnd extends AbstractProductTestCase
{
    const PRODUCT_UUID = '412eb420-e4d1-4997-94d4-70be156a33a8';

    public function test_it_gets_a_product_with_attribute_options_simple_select()
    {
        $this->createProductWithUuid(self::PRODUCT_UUID, [
            new SetIdentifierValue('sku', 'product'),
            new SetFamily('familyA'),
            new SetSimpleSelectValue('a_simple_select', null, null, 'optionA')
        ]);

        $client = $this->createAuthenticatedClient();
        $client->request('GET', 'api/rest/v1/products/product?with_attribute_options=true');

        $expectedProduct = [
            'uuid' => self::PRODUCT_UUID,
            'identifier' => 'product',
            'family' => 'familyA',
            'parent' => null,
            'groups' => [],
            'categories' => [],
            'enabled' => true,
            'values' => [
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
            'created' => '2016-06-14T13:12:50+02:00',
            'updated' => '2016-06-14T13:12:50+02:00',
            'associations' => [
                'PACK' => ['groups' => [], 'products' => [], 'product_models' => []],
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
        $this->createProductWithUuid(self::PRODUCT_UUID, [
            new SetIdentifierValue('sku', 'product'),
            new SetFamily('familyA'),
            new SetMultiSelectValue('a_multi_select', null, null, ['optionA', 'optionB'])
        ]);

        $client = $this->createAuthenticatedClient();
        $client->request('GET', 'api/rest/v1/products/product?with_attribute_options=true');

        $expectedProduct = [
            'uuid' => self::PRODUCT_UUID,
            'identifier' => 'product',
            'family' => 'familyA',
            'parent' => null,
            'groups' => [],
            'categories' => [],
            'enabled' => true,
            'values' => [
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
            'created' => '2016-06-14T13:12:50+02:00',
            'updated' => '2016-06-14T13:12:50+02:00',
            'associations' => [
                'PACK' => ['groups' => [], 'products' => [], 'product_models' => []],
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

    public function test_it_gets_a_product_with_attribute_options_multi_select_with_wrong_case()
    {
        $this->createProductWithUuid(self::PRODUCT_UUID, [
            new SetIdentifierValue('sku', 'product'),
            new SetFamily('familyA'),
            new SetMultiSelectValue('a_multi_select', null, null, ['optionA', 'OptiONB'])
        ]);

        $client = $this->createAuthenticatedClient();
        $client->request('GET', 'api/rest/v1/products/product?with_attribute_options=true');

        $expectedProduct = [
            'uuid' => self::PRODUCT_UUID,
            'identifier' => 'product',
            'family' => 'familyA',
            'parent' => null,
            'groups' => [],
            'categories' => [],
            'enabled' => true,
            'values' => [
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
            'created' => '2016-06-14T13:12:50+02:00',
            'updated' => '2016-06-14T13:12:50+02:00',
            'associations' => [
                'PACK' => ['groups' => [], 'products' => [], 'product_models' => []],
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
        $this->createProductWithUuid(self::PRODUCT_UUID, [
            new SetIdentifierValue('sku', 'product'),
            new SetFamily('familyA1'),
            new SetEnabled(true),
            new SetCategories(['categoryA', 'master', 'master_china']),
            new SetGroups(['groupA', 'groupB']),
            new SetDateValue('a_date', null, null, new \DateTime('2016-06-28'))
        ]);

        $client = $this->createAuthenticatedClient();
        $client->request('GET', 'api/rest/v1/products/product');

        $standardProduct = [
            'uuid' => self::PRODUCT_UUID,
            'identifier' => 'product',
            'family' => 'familyA1',
            'parent' => null,
            'groups' => ['groupA', 'groupB'],
            'categories' => ['categoryA', 'master', 'master_china'],
            'enabled' => true,
            'values' => [
                'a_date' => [
                    ['data' => '2016-06-28T00:00:00+02:00', 'locale' => null, 'scope' => null]
                ],
            ],
            'created' => '2016-06-14T13:12:50+02:00',
            'updated' => '2016-06-14T13:12:50+02:00',
            'associations' => [
                'PACK' => ['groups' => [], 'products' => [], 'product_models' => []],
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
        $product = $this->createProductWithUuid(self::PRODUCT_UUID, [
            new SetIdentifierValue('sku', 'product'),
            new SetFamily('familyA')
        ]);

        $this->get(EvaluateProducts::class)->forPendingCriteria(
            ProductUuidCollection::fromString($product->getUuid()->toString())
        );

        $client = $this->createAuthenticatedClient();
        $client->request('GET', 'api/rest/v1/products/product?with_quality_scores=true');

        $expectedProduct = [
            'uuid' => self::PRODUCT_UUID,
            'identifier' => 'product',
            'family' => 'familyA',
            'parent' => null,
            'groups' => [],
            'categories' => [],
            'enabled' => true,
            'values' => [],
            'created' => '2016-06-14T13:12:50+02:00',
            'updated' => '2016-06-14T13:12:50+02:00',
            'associations' => [
                'PACK' => ['groups' => [], 'products' => [], 'product_models' => []],
                'SUBSTITUTION' => ['groups' => [], 'products' => [], 'product_models' => []],
                'UPSELL' => ['groups' => [], 'products' => [], 'product_models' => []],
                'X_SELL' => ['groups' => [], 'products' => [], 'product_models' => []],
            ],
            'quantified_associations' => [],
            'quality_scores' => [
                ['scope' => 'tablet', 'locale' => 'de_DE', 'data' => 'E',],
                ['scope' => 'tablet', 'locale' => 'en_US', 'data' => 'E',],
                ['scope' => 'tablet', 'locale' => 'fr_FR', 'data' => 'E',],
                ['scope' => 'ecommerce', 'locale' => 'en_US', 'data' => 'E',],
                ['scope' => 'ecommerce_china', 'locale' => 'en_US', 'data' => 'E',],
                ['scope' => 'ecommerce_china', 'locale' => 'zh_CN', 'data' => 'E',],
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertResponse($response, $expectedProduct);
    }

    public function test_it_gets_a_product_with_completenesses()
    {
        $this->createProductWithUuid(self::PRODUCT_UUID, [
            new SetIdentifierValue('sku', 'product'),
            new SetFamily('familyA'),
            new SetSimpleSelectValue('a_simple_select', null, null, 'optionA')
        ]);

        $client = $this->createAuthenticatedClient();
        $client->request('GET', 'api/rest/v1/products/product?with_completenesses=true');

        $expectedProduct = [
            'uuid' => self::PRODUCT_UUID,
            'identifier' => 'product',
            'family' => 'familyA',
            'parent' => null,
            'groups' => [],
            'categories' => [],
            'enabled' => true,
            'values' => [
                'a_simple_select' => [
                    [
                        'data' => 'optionA',
                        'locale' => null,
                        'scope' => null,
                    ],
                ],
            ],
            'created' => '2016-06-14T13:12:50+02:00',
            'updated' => '2016-06-14T13:12:50+02:00',
            'associations' => [
                'PACK' => ['groups' => [], 'products' => [], 'product_models' => []],
                'SUBSTITUTION' => ['groups' => [], 'products' => [], 'product_models' => []],
                'UPSELL' => ['groups' => [], 'products' => [], 'product_models' => []],
                'X_SELL' => ['groups' => [], 'products' => [], 'product_models' => []],
            ],
            'quantified_associations' => [],
            'completenesses' => [
                ['scope' => 'ecommerce', 'locale' => 'en_US', 'data' => 10],
                ['scope' => 'ecommerce_china', 'locale' => 'en_US', 'data' => 100],
                ['scope' => 'ecommerce_china', 'locale' => 'zh_CN', 'data' => 100],
                ['scope' => 'tablet', 'locale' => 'de_DE', 'data' => 10],
                ['scope' => 'tablet', 'locale' => 'en_US', 'data' => 10],
                ['scope' => 'tablet', 'locale' => 'fr_FR', 'data' => 10],
            ]
        ];

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertResponse($response, $expectedProduct);
    }

    public function testAccessDeniedWhenRetrievingProductWithoutTheAcl()
    {
        $this->createProduct('product', [new SetFamily('familyA')]);
        $client = $this->createAuthenticatedClient();
        $this->removeAclFromRole('action:pim_api_product_list');

        $client->request('GET', 'api/rest/v1/products/product');
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
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
