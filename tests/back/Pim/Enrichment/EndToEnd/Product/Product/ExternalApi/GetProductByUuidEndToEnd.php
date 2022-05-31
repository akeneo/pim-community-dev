<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\SetGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetDateValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Test\Integration\Configuration;
use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedProductCleaner;
use Symfony\Component\HttpFoundation\Response;

class GetProductByUuidEndToEnd extends AbstractProductTestCase
{

    public function SKIPOKtest_it_gets_a_product_with_attribute_options_simple_select()
    {
        /** @var Product $product */
        $product = $this->createProduct('product', [
            new SetFamily('familyA'),
            new SetSimpleSelectValue('a_simple_select', null, null, 'optionA')
        ]);

        $client = $this->createAuthenticatedClient();
        $client->request('GET', sprintf('api/rest/v1/products-uuid/%s?with_attribute_options=true', $product->getUuid()->toString()));

        $expectedProduct = [
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

    public function SKIPOKtest_it_gets_a_product_with_attribute_options_multi_select()
    {
        /** @var Product $product */
        $product = $this->createProduct('product', [
            new SetFamily('familyA'),
            new SetMultiSelectValue('a_multi_select', null, null, ['optionA', 'optionB'])
        ]);

        $client = $this->createAuthenticatedClient();
        $client->request('GET', sprintf('api/rest/v1/products-uuid/%s?with_attribute_options=true', $product->getUuid()->toString()));

        $expectedProduct = [
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
    public function SKIPOKtest_it_gets_a_product()
    {
        /** @var Product $product */
        $product = $this->createProduct('product', [
            new SetFamily('familyA1'),
            new SetEnabled(true),
            new SetCategories(['categoryA', 'master', 'master_china']),
            new SetGroups(['groupA', 'groupB']),
            new SetDateValue('a_date', null, null, new \DateTime('2016-06-28'))
        ]);

        $client = $this->createAuthenticatedClient();
        $client->request('GET', sprintf('api/rest/v1/products-uuid/%s', $product->getUuid()->toString()));

        $standardProduct = [
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

    public function SKIPOKtest_it_throws_a_404_response_when_the_product_is_not_found()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products-uuid/8801f40b-8a41-4446-a894-777de9b3b6e8');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content, 'response contains 2 items');
        $this->assertSame(Response::HTTP_NOT_FOUND, $content['code']);
        $this->assertSame('Product "8801f40b-8a41-4446-a894-777de9b3b6e8" does not exist or you do not have permission to access it.', $content['message']);
    }

    public function test_it_gets_a_product_with_quality_scores()
    {
        /** @var Product $product */
        $product = $this->createProduct('product', [new SetFamily('familyA')]);

        ($this->get('Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProducts'))(
            ProductUuidCollection::fromProductUuid(ProductUuid::fromUuid($product->getUuid()))
        );

        $client = $this->createAuthenticatedClient();
        $client->request('GET', sprintf('api/rest/v1/products-uuid/%s?with_quality_scores=true', $product->getUuid()->toString()));

        $expectedProduct = [
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

    public function SKIPtest_it_gets_a_product_with_completenesses()
    {
        /** @var Product $product */
        $product = $this->createProduct('product', [
            new SetFamily('familyA'),
            new SetSimpleSelectValue('a_simple_select', null, null, 'optionA')
        ]);

        $client = $this->createAuthenticatedClient();
        $client->request('GET', sprintf('api/rest/v1/products-uuid/%s?with_completenesses=true', $product->getUuid()->toString()));

        $expectedProduct = [
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

    public function SKIPtestAccessDeniedWhenRetrievingProductWithoutTheAcl()
    {
        /** @var Product $product */
        $product = $this->createProduct('product', [new SetFamily('familyA')]);
        $client = $this->createAuthenticatedClient();
        $this->removeAclFromRole('action:pim_api_product_list');

        $client->request('GET', sprintf('api/rest/v1/products-uuid/%s', $product->getUuid()->toString()));
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function SKIPtest_it_gets_a_product_with_associations_and_quantified_associations()
    {
        /** @var Product $productAssociated */
        $productAssociated = $this->createProduct('productAssociated', [
            new SetFamily('familyB'),
            new SetSimpleSelectValue('a_simple_select', null, null, 'optionA'),
        ]);

        /** @var Product $productAssociated */
        $otherProductAssociated = $this->createProduct('otherProductAssociated', [
            new SetFamily('familyA'),
            new SetSimpleSelectValue('a_simple_select', null, null, 'optionB'),
        ]);

        $this->createProductModel([
            'code' => 'productModelAssociated',
            'family_variant' => 'familyVariantA1',
            'parent' => 'tshirt',
            'values' => [
                'a_simple_select' => [
                    [
                        'scope'  => null,
                        'locale' => null,
                        'data'   => "optionB",
                    ],
                ],
            ],
        ]);

        /** @var Product $product */
        $product = $this->createProduct('product', [
            new SetFamily('familyA'),
            new SetSimpleSelectValue('a_simple_select', null, null, 'optionA'),
            new AssociateGroups('PACK', ['groupA', 'groupB']),
            new AssociateProductModels('SUBSTITUTION', ['productModelAssociated']),
            new AssociateProducts('UPSELL', ['productAssociated']),
            new AssociateProducts('X_SELL', ['otherProductAssociated']),
        ]);

        $client = $this->createAuthenticatedClient();
        $client->request('GET', sprintf('api/rest/v1/products-uuid/%s?with_completenesses=true', $product->getUuid()->toString()));

        $expectedProduct = [
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
                'PACK' => ['groups' => ['groupA', 'groupB'], 'products' => [], 'product_models' => []],
                'SUBSTITUTION' => ['groups' => [], 'products' => [], 'product_models' => ['productModelAssociated']],
                'UPSELL' => ['groups' => [], 'products' => [$productAssociated->getUuid()->toString()], 'product_models' => []],
                'X_SELL' => ['groups' => [], 'products' => [$otherProductAssociated->getUuid()->toString()], 'product_models' => []],
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
