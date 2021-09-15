<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Test\Integration\Infrastructure\Controller;

use Akeneo\Platform\TailoredExport\Test\Integration\ControllerIntegrationTestCase;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

final class GetGroupedSourcesControllerIntegrationTest extends ControllerIntegrationTestCase
{
    private const ROUTE = 'pimee_tailored_export_get_product_grouped_sources_action';
    private WebClientHelper $webClientHelper;

    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn($this->client, 'julia');
        $this->webClientHelper = $this->get('akeneo_integration_tests.helper.web_client');
    }

    public function test_it_returns_the_first_page_of_available_sources(): void
    {
        $response = $this->assertCallSuccess(4, ['attribute' => 0, 'system' => 0, 'association_type' => 0]);
        $sourceGroups = \json_decode($response->getContent(), true)['results'];

        $this->assertNotEmpty($sourceGroups);
        $this->assertArrayHasKey('code', $sourceGroups[0]);
        $this->assertArrayHasKey('label', $sourceGroups[0]);
        $this->assertArrayHasKey('children', $sourceGroups[0]);

        $this->assertSame('system', $sourceGroups[0]['code']);
        $this->assertSame('System', $sourceGroups[0]['label']);

        $source = $sourceGroups[0]['children'][0];
        $this->assertArrayHasKey('code', $source);
        $this->assertArrayHasKey('label', $source);
        $this->assertArrayHasKey('type', $source);

        $this->assertSame('categories', $source['code']);
        $this->assertSame('property', $source['type']);
        $this->assertSame('Categories', $source['label']);

        $this->assertFiltersCount(4, $sourceGroups);
    }

    public function test_it_returns_system_filters_and_attribute_filters(): void
    {
        $response = $this->assertCallSuccess(100, ['attribute' => 0, 'system' => 0, 'association_type' => 0]);
        $sourceGroups = \json_decode($response->getContent(), true)['results'];
        $this->assertNotEmpty($sourceGroups);
        $this->assertGreaterThan(1, count($sourceGroups));

        $this->assertSame('system', $sourceGroups[0]['code']);
        $this->assertSame('System', $sourceGroups[0]['label']);
        $this->assertSame('association_types', $sourceGroups[1]['code']);
        $this->assertSame('Association types', $sourceGroups[1]['label']);
        $this->assertSame('erp', $sourceGroups[2]['code']);
        $this->assertSame('ERP', $sourceGroups[2]['label']);

        $this->assertSourceGroupContainSource('system', 'family', $sourceGroups);
        $this->assertSourceGroupContainSource('association_types', 'X_SELL', $sourceGroups);
        $this->assertSourceGroupContainSource('erp', 'ean', $sourceGroups);
        $this->assertSourceGroupContainSource('erp', 'erp_name', $sourceGroups);
        $this->assertSourceGroupContainSource('marketing', 'name', $sourceGroups);
    }

    public function test_it_paginates_the_results(): void
    {
        $response = $this->assertCallSuccess(4, ['attribute' => 0, 'system' => 10, 'association_type' => 0]);
        $sourceGroups = \json_decode($response->getContent(), true)['results'];
        $this->assertNotEmpty($sourceGroups);

        $this->assertSourceGroupDoNotContainSource('system', 'family', $sourceGroups);

        $response = $this->assertCallSuccess(10, ['attribute' => 1000, 'system' => 1000, 'association_type' => 1000]);
        $sourceGroups = \json_decode($response->getContent(), true)['results'];
        $this->assertEmpty($sourceGroups);
    }

    public function test_it_can_search_by_text(): void
    {
        $response = $this->assertCallSuccess(6, ['attribute' => 0, 'system' => 0, 'association_type' => 0], 'am');
        $sourceGroups = \json_decode($response->getContent(), true)['results'];
        $this->assertNotEmpty($sourceGroups);

        $this->assertSourceGroupDoNotContainSource('system', 'categories', $sourceGroups);
        $this->assertSourceGroupContainSource('system', 'family', $sourceGroups);
        $this->assertSourceGroupContainSource('erp', 'erp_name', $sourceGroups);
        $this->assertSourceGroupContainSource('marketing', 'name', $sourceGroups);
        $this->assertSourceGroupContainSource('marketing', 'variation_name', $sourceGroups);

        $response = $this->assertCallSuccess(4, ['attribute' => 0, 'system' => 0, 'association_type' => 0], 'X_SELL');
        $sourceGroups = \json_decode($response->getContent(), true)['results'];
        $this->assertNotEmpty($sourceGroups);
        $this->assertSourceGroupDoNotContainSource('system', 'categories', $sourceGroups);
        $this->assertSourceGroupContainSource('association_types', 'X_SELL', $sourceGroups);
        $this->assertSourceGroupDoNotContainSource('association_types', 'UPSELL', $sourceGroups);
        $this->assertSourceGroupDoNotContainSource('marketing', 'name', $sourceGroups);

        $response = $this->assertCallSuccess(4, ['attribute' => 0, 'system' => 0, 'association_type' => 0], 'erp name');
        $sourceGroups = \json_decode($response->getContent(), true)['results'];
        $this->assertNotEmpty($sourceGroups);

        $this->assertSourceGroupDoNotContainSource('system', 'family', $sourceGroups);
        $this->assertSourceGroupContainSource('erp', 'erp_name', $sourceGroups);
        $this->assertSourceGroupDoNotContainSource('marketing', 'name', $sourceGroups);
    }

    public function test_it_translates_the_labels(): void
    {
        $response = $this->assertCallSuccess(4, ['attribute' => 0, 'system' => 0, 'association_type' => 0]);
        $sourceGroups = \json_decode($response->getContent(), true)['results'];
        $this->assertNotEmpty($sourceGroups);

        foreach ($sourceGroups as $group) {
            if ($group['code'] === 'system') {
                $this->assertSame('System', $group['label']);
            }

            foreach ($group['children'] as $filter) {
                if ($filter['code'] === 'erp_name') {
                    $this->assertSame('ERP name', $filter['label']);
                }
            }
        }
    }

    private function assertCallSuccess(int $limit, array $offset, string $search = null): Response
    {
        $options = [
            'limit' => $limit,
            'offset' => $offset,
        ];

        $this->webClientHelper->callApiRoute(
            $this->client,
            self::ROUTE,
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

    private function assertFiltersCount(int $expectedCount, array $sourceGroups): void
    {
        $totalCount = array_reduce($sourceGroups, function (int $totalCount, array $group): int {
            return $totalCount + count($group['children']);
        }, 0);

        $this->assertSame($expectedCount, $totalCount);
    }

    private function assertSourceGroupContainSource(
        string $expectedSourceGroupCode,
        string $expectedSourceCode,
        array $sourceGroups
    ): void {
        $this->assertTrue($this->isSourceInSourceGroup($expectedSourceGroupCode, $expectedSourceCode, $sourceGroups), sprintf(
            'The "%s" source code in "%s" source group code is not found in results.',
            $expectedSourceCode,
            $expectedSourceGroupCode,
        ));
    }

    private function assertSourceGroupDoNotContainSource(
        string $expectedSourceGroupCode,
        string $expectedSourceCode,
        array $sourceGroups
    ): void {
        $this->assertFalse($this->isSourceInSourceGroup($expectedSourceGroupCode, $expectedSourceCode, $sourceGroups), sprintf(
            'The "%s" source code in "%s" source group code is found in results.',
            $expectedSourceCode,
            $expectedSourceGroupCode,
        ));
    }

    private function isSourceInSourceGroup(
        string $expectedSourceGroupCode,
        string $expectedSourceCode,
        array $sourceGroups
    ): bool {
        foreach ($sourceGroups as $group) {
            if ($group['code'] !== $expectedSourceGroupCode) {
                continue;
            }

            foreach ($group['children'] as $filter) {
                if ($filter['code'] === $expectedSourceCode) {
                    return true;
                }
            }
        }

        return false;
    }
}
