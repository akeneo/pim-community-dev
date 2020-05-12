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

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class GetAvailableConditionFieldsControllerIntegration extends ControllerIntegrationTestCase
{
    private const ROUTE = 'pimee_enrich_rule_definition_get_available_condition_fields';

    /** @var WebClientHelper  */
    private $webClientHelper;

    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn($this->client, 'julia');
        $this->webClientHelper = $this->get('akeneo_integration_tests.helper.web_client');
    }

    public function test_it_returns_the_first_page_of_available_condition_fields(): void
    {
        $response = $this->assertCallSuccess(4, 1);
        $filters = \json_decode($response->getContent(), true);

        $this->assertNotEmpty($filters);
        $this->assertArrayHasKey('id', $filters[0]);
        $this->assertArrayHasKey('text', $filters[0]);
        $this->assertArrayHasKey('children', $filters[0]);

        $this->assertSame('system', $filters[0]['id']);
        $this->assertSame('System', $filters[0]['text']);

        $child = $filters[0]['children'][0];
        $this->assertArrayHasKey('id', $child);
        $this->assertArrayHasKey('text', $child);

        $this->assertFiltersCount(4, $filters);
    }

    public function test_it_returns_system_filters_and_attribute_filters(): void
    {
        $response = $this->assertCallSuccess(100, 1);
        $filters = \json_decode($response->getContent(), true);
        $this->assertNotEmpty($filters);
        $this->assertGreaterThan(1, count($filters));

        $this->assertSame('system', $filters[0]['id']);
        $this->assertSame('System', $filters[0]['text']);
        $this->assertSame('erp', $filters[1]['id']);
        $this->assertSame('ERP', $filters[1]['text']);

        $this->assertResultsContainFilter('system', 'family', $filters);
        $this->assertResultsContainFilter('erp', 'ean', $filters);
        $this->assertResultsContainFilter('erp', 'erp_name', $filters);
        $this->assertResultsContainFilter('marketing', 'name', $filters);
    }

    public function test_it_paginates_the_results(): void
    {
        $response = $this->assertCallSuccess(4, 2);
        $filters = \json_decode($response->getContent(), true);
        $this->assertNotEmpty($filters);

        $this->assertResultsDoNotContainFilter('system', 'family', $filters);

        $response = $this->assertCallSuccess(10, 1000);
        $filters = \json_decode($response->getContent(), true);
        $this->assertEmpty($filters);
    }

    public function test_it_can_search_by_text(): void
    {
        $response = $this->assertCallSuccess(4, 1, 'name');
        $filters = \json_decode($response->getContent(), true);
        $this->assertNotEmpty($filters);

        $this->assertResultsDoNotContainFilter('system', 'family', $filters);
        $this->assertResultsContainFilter('erp', 'erp_name', $filters);
        $this->assertResultsContainFilter('marketing', 'name', $filters);
        $this->assertResultsContainFilter('marketing', 'variation_name', $filters);

        $response = $this->assertCallSuccess(4, 1, 'erp name');
        $filters = \json_decode($response->getContent(), true);
        $this->assertNotEmpty($filters);

        $this->assertResultsDoNotContainFilter('system', 'family', $filters);
        $this->assertResultsContainFilter('erp', 'erp_name', $filters);
        $this->assertResultsDoNotContainFilter('marketing', 'name', $filters);
    }

    public function test_it_translates_the_labels(): void
    {
        $response = $this->assertCallSuccess(4, 1);
        $filters = \json_decode($response->getContent(), true);
        $this->assertNotEmpty($filters);

        foreach ($filters as $group) {
            if ($group['id'] === 'system') {
                $this->assertSame('System', $group['text']);
            }

            foreach ($group['children'] as $filter) {
                if ($filter['id'] === 'erp_name') {
                    $this->assertSame('ERP name', $filter['text']);
                }
            }
        }
    }

    private function assertCallSuccess(int $limit, int $page, string $search = null): Response
    {
        $options = ['limit' => $limit, 'page' => $page];
        $this->webClientHelper->callApiRoute(
            $this->client,
            static::ROUTE,
            [],
            'GET',
            ['options' => $options, 'search' => $search]
        );

        $response = $this->client->getResponse();
        Assert::assertSame($response->getStatusCode(), Response::HTTP_OK);

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    private function assertFiltersCount(int $expectedCount, array $filters): void
    {
        $totalCount = array_reduce($filters, function (int $totalCount, array $group): int {
            return $totalCount + count($group['children']);
        }, 0);

        $this->assertSame($expectedCount, $totalCount);
    }

    private function assertResultsContainFilter(
        string $expectedGroupCode,
        string $expectedFieldCode,
        array $filters
    ): void {
        $this->assertTrue($this->isFilterInResults($expectedGroupCode, $expectedFieldCode, $filters), sprintf(
            'The "%s" field code in "%s" group code is not found in results.',
            $expectedFieldCode,
            $expectedGroupCode,
        ));
    }

    private function assertResultsDoNotContainFilter(
        string $expectedGroupCode,
        string $expectedFieldCode,
        array $filters
    ): void {
        $this->assertFalse($this->isFilterInResults($expectedGroupCode, $expectedFieldCode, $filters), sprintf(
            'The "%s" field code in "%s" group code is found in results.',
            $expectedFieldCode,
            $expectedGroupCode,
        ));
    }

    private function isFilterInResults(
        string $expectedGroupCode,
        string $expectedFieldCode,
        array $filters
    ): bool {
        $found = false;
        foreach ($filters as $group) {
            if ($group['id'] === $expectedGroupCode) {
                foreach ($group['children'] as $filter) {
                    if ($filter['id'] === $expectedFieldCode) {
                        $found = true;

                        break 2;
                    }
                }
            }
        }

        return $found;
    }
}
