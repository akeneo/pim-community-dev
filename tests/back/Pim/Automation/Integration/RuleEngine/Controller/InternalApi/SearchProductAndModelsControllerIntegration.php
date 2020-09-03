<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\Automation\Integration\RuleEngine\Controller\InternalApi;

use Akeneo\Test\Integration\Configuration;
use AkeneoEnterprise\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use AkeneoTestEnterprise\Pim\Automation\Integration\ControllerIntegrationTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class SearchProductAndModelsControllerIntegration extends ControllerIntegrationTestCase
{
    private const ROUTE = 'pimee_enrich_rule_definition_search_products_and_product_models';

    /** @var WebClientHelper */
    private $webClientHelper;

    /**
     * @test
     */
    public function it_gets_all_identifiers()
    {
        $this->assertIdentifiers(
            ['abc123', 'def123', '123456', 'sub_abc123', 'model_123', 'model_abcdef', 'model_abc123']
        );
    }

    /**
     * @test
     */
    public function it_gets_identifiers_by_search()
    {
        $this->assertIdentifiers(
            ['abc123', 'sub_abc123', 'model_abcdef', 'model_abc123'],
            'abc'
        );
    }

    /**
     * @test
     */
    public function it_gets_product_identifiers()
    {
        $this->assertIdentifiers(
            ['abc123', 'def123', '123456', 'sub_abc123'],
            null,
            'product'
        );
    }

    /**
     * @test
     */
    public function it_gets_product_model_codes()
    {
        $this->assertIdentifiers(
            ['model_123', 'model_abcdef', 'model_abc123'],
            null,
            'product_model'
        );
    }

    /**
     * @test
     */
    public function it_paginates_results()
    {
        $this->assertIdentifiers(
            ['abc123', 'def123', '123456'],
            null,
            null,
            1,
            3
        );
        $this->assertIdentifiers(
            ['sub_abc123', 'model_123', 'model_abcdef'],
            null,
            null,
            2,
            3
        );
        $this->assertIdentifiers(
            ['model_abc123'],
            null,
            null,
            3,
            3
        );
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures();
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn($this->client, 'julia');
        $this->webClientHelper = $this->get('akeneo_integration_tests.helper.web_client');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function assertIdentifiers(
        array $expectedIdentifiers,
        ?string $search = null,
        ?string $entityType = null,
        ?int $page = 1,
        ?int $limit = 20
    ): void {
        $parameters = [
            'search' => $search,
            'options' => [
                'page' => $page,
                'limit' => $limit,
                'type' => $entityType,
            ],
        ];
        $this->webClientHelper->callApiRoute(
            $this->client,
            static::ROUTE,
            [],
            'GET',
            $parameters
        );

        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());
        Assert::assertSame(
            [
                'results' => array_map(
                    function (string $expectedIdentifier): array {
                        return [
                            'id' => $expectedIdentifier,
                            'text' => $expectedIdentifier,
                        ];
                    },
                    $expectedIdentifiers
                ),
            ],
            \json_decode($response->getContent(), true)
        );
    }

    private function loadFixtures(): void
    {
        $this->createProduct('abc123');
        $this->createProduct('def123');
        $this->createProduct('123456');

        $this->createProductModel('model_123');
        $this->createProductModel(
            'model_abcdef',
            [
                'parent' => 'model_123',
                'values' => [
                    'a_simple_select' => [['locale' => null, 'scope' => null, 'data' => 'optionA']],
                ],
            ]
        );
        $this->createProductModel(
            'model_abc123',
            [
                'parent' => 'model_123',
                'values' => [
                    'a_simple_select' => [['locale' => null, 'scope' => null, 'data' => 'optionB']],
                ],
            ]
        );
        $this->createProduct(
            'sub_abc123',
            [
                'parent' => 'model_abc123',
                'values' => [
                    'a_yes_no' => [['locale' => null, 'scope' => null, 'data' => true]],
                ],
            ]
        );

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    private function createProduct(string $identifier, array $data = []): void
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $violations = $this->get('pim_catalog.validator.product')->validate($product);
        Assert::assertCount(0, $violations, \sprintf('The product is not valid: %s', $violations));
        $this->get('pim_catalog.saver.product')->save($product);
    }

    private function createProductModel(string $code, array $data = []): void
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update(
            $productModel,
            array_merge(
                ['code' => $code, 'family_variant' => 'familyVariantA1'],
                $data
            )
        );
        $violations = $this->get('pim_catalog.validator.product_model')->validate($productModel);
        Assert::assertCount(0, $violations, \sprintf('The product model is not valid: %s', $violations));
        $this->get('pim_catalog.saver.product_model')->save($productModel);
    }
}
