<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\Automation\Integration\RuleEngine\Controller\InternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use AkeneoTestEnterprise\Pim\Automation\Integration\ControllerIntegrationTestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class GetCategoryTreeControllerIntegration extends ControllerIntegrationTestCase
{
    private const ROUTE = 'pimee_enrich_rule_definition_get_category_tree';

    private WebClientHelper $webClientHelper;

    public function test_it_returns_an_empty_array_if_no_category_is_selected(): void
    {
        $response = $this->getCategoryTreeResponse([]);
        Assert::assertSame($response->getStatusCode(), Response::HTTP_OK);
        Assert::assertSame(\json_encode([]), \trim($response->getContent()));
    }

    function test_it_gets_selected_categories(): void
    {
        $response = $this->getCategoryTreeResponse(['categoryA', 'categoryA2']);
        Assert::assertSame($response->getStatusCode(), Response::HTTP_OK);
        $categoryTreeInfo = \json_decode($response->getContent(), true);
        Assert::assertIsArray($categoryTreeInfo);
        Assert::assertCount(1, $categoryTreeInfo);
        Assert::assertIsArray($categoryTreeInfo[0]);
        Assert::assertArrayHasKey('children',$categoryTreeInfo[0]);
        Assert::assertCount(3, $categoryTreeInfo[0]['children']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('julia', $this->client);
        $this->webClientHelper = $this->get('akeneo_integration_tests.helper.web_client');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getCategoryTreeResponse(array $selectedCategoryCodes = []): Response
    {
        $masterCategoryId = $this->getCategoryIds(['master'])[0];
        $selectedCategoryIds = [] === $selectedCategoryCodes ? [] : $this->getCategoryIds($selectedCategoryCodes);

        $this->webClientHelper->callApiRoute(
            $this->client,
            static::ROUTE,
            ['categoryTreeId' => $masterCategoryId],
            'GET',
            ['selected' => $selectedCategoryIds]
        );

        return $this->client->getResponse();
    }

    private function getCategoryIds(array $categoryCodes): array
    {
        $res = $this->get('database_connection')->executeQuery(
            'SELECT id from pim_catalog_category where code IN (:codes)',
            ['codes' => $categoryCodes],
            ['codes' => Connection::PARAM_STR_ARRAY]
        )->fetchFirstColumn();

        return \array_map(
            fn (string $id): int => (int) $id,
            $res
        );
    }
}
