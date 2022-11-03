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
use Akeneo\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use AkeneoTestEnterprise\Pim\Automation\Integration\ControllerIntegrationTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class GetImpactedProductCountControllerIntegration extends ControllerIntegrationTestCase
{
    private const ROUTE = 'pimee_enrich_rule_definition_get_impacted_product_count';

    /** @var WebClientHelper  */
    private $webClientHelper;

    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn($this->client, 'julia');
        $this->webClientHelper = $this->get('akeneo_integration_tests.helper.web_client');
    }

    public function test_it_returns_the_impacted_product_count_for_a_condition_on_clothing_family()
    {
        $normalizedConditions = [
            [
                'field' => 'family',
                'operator' => 'IN',
                'value' => ['clothing'],
            ],
        ];

        $this->assertImpactedProductCount($normalizedConditions, 169, 61);
    }

    public function test_it_returns_the_impacted_product_count_for_a_condition_on_shoes_family()
    {
        $normalizedConditions = [
            [
                'field' => 'family',
                'operator' => 'IN',
                'value' => ['shoes'],
            ],
        ];

        $this->assertImpactedProductCount($normalizedConditions, 65, 18);
    }

    public function test_it_returns_a_400_response_with_invalid_condition()
    {
        $invalidCondition = [
            'field' => 'family',
            'operator' => 'IN',
        ];
        $this->webClientHelper->callApiRoute(
            $this->client,
            static::ROUTE,
            [],
            'POST',
            ['conditions' => \json_encode([$invalidCondition])]
        );

        Assert::assertSame(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    private function assertImpactedProductCount(array $conditions, int $expectedProductCount, int $expectedProductModelCount)
    {
        $this->webClientHelper->callApiRoute(
            $this->client,
            static::ROUTE,
            [],
            'POST',
            ['conditions' => \json_encode($conditions)]
        );

        $response = $this->client->getResponse();
        Assert::assertSame($response->getStatusCode(), Response::HTTP_OK);

        $content = \json_decode($response->getContent(), true);
        Assert::assertArrayHasKey('impacted_product_count', $content);
        Assert::assertSame($expectedProductCount, $content['impacted_product_count']);
        Assert::assertArrayHasKey('impacted_product_model_count', $content);
        Assert::assertSame($expectedProductModelCount, $content['impacted_product_model_count']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
