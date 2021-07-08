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

namespace Akeneo\Pim\TailoredExport\Test\Integration\Infrastructure\Controller;

use Akeneo\Pim\TailoredExport\Test\Integration\ControllerIntegrationTestCase;
use Akeneo\Test\Integration\Configuration;
use AkeneoEnterprise\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

final class GetAvailableFieldsControllerIntegration extends ControllerIntegrationTestCase
{
    private const ROUTE = 'pimee_tailored_export_get_grouped_sources_action';
    private WebClientHelper $webClientHelper;

    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn($this->client, 'julia');
        $this->webClientHelper = $this->get('akeneo_integration_tests.helper.web_client');
    }

    public function test_it_returns_the_first_page_of_available_fields(): void
    {
        $response = $this->assertCallSuccess(4, 0);
        $sources = \json_decode($response->getContent(), true);

        $this->assertNotEmpty($sources);
        $this->assertArrayHasKey('code', $sources[0]);
        $this->assertArrayHasKey('label', $sources[0]);
        $this->assertArrayHasKey('children', $sources[0]);

        $this->assertSame('system', $sources[0]['code']);
        $this->assertSame('System', $sources[0]['label']);

        $child = $sources[0]['children'][0];
        $this->assertArrayHasKey('code', $child);
        $this->assertArrayHasKey('label', $child);
        $this->assertArrayHasKey('type', $child);

        $this->assertSame('categories', $child['code']);
        $this->assertSame('property', $child['type']);
        $this->assertSame('Categories', $child['label']);

        $this->assertFiltersCount(4, $sources);
    }

    public function test_it_returns_system_filters_and_attribute_filters(): void
    {
        $response = $this->assertCallSuccess(100, 0);
        $sources = \json_decode($response->getContent(), true);
        $this->assertNotEmpty($sources);
        $this->assertGreaterThan(1, count($sources));

        $this->assertSame('system', $sources[0]['code']);
        $this->assertSame('System', $sources[0]['label']);
        $this->assertSame('associations', $sources[1]['code']);
        $this->assertSame('Associations', $sources[1]['label']);
        $this->assertSame('erp', $sources[2]['code']);
        $this->assertSame('ERP', $sources[2]['label']);

        $this->assertResultsContainFilter('system', 'family', $sources);
        $this->assertResultsContainFilter('associations', 'X_SELL', $sources);
        $this->assertResultsContainFilter('erp', 'ean', $sources);
        $this->assertResultsContainFilter('erp', 'erp_name', $sources);
        $this->assertResultsContainFilter('marketing', 'name', $sources);
    }

    public function test_it_paginates_the_results(): void
    {
        $response = $this->assertCallSuccess(4, 1);
        $sources = \json_decode($response->getContent(), true);
        $this->assertNotEmpty($sources);

        $this->assertResultsDoNotContainFilter('system', 'family', $sources);

        $response = $this->assertCallSuccess(10, 1000);
        $sources = \json_decode($response->getContent(), true);
        $this->assertEmpty($sources);
    }

    public function test_it_can_search_by_text(): void
    {
        $response = $this->assertCallSuccess(6, 0, null, 'am');
        $sources = \json_decode($response->getContent(), true);
        $this->assertNotEmpty($sources);

        $this->assertResultsDoNotContainFilter('system', 'categories', $sources);
        $this->assertResultsContainFilter('system', 'family', $sources);
        $this->assertResultsContainFilter('erp', 'erp_name', $sources);
        $this->assertResultsContainFilter('marketing', 'name', $sources);
        $this->assertResultsContainFilter('marketing', 'variation_name', $sources);

        $response = $this->assertCallSuccess(4, 0, null, 'X_SELL');
        $sources = \json_decode($response->getContent(), true);
        $this->assertNotEmpty($sources);
        $this->assertResultsDoNotContainFilter('system', 'categories', $sources);
        $this->assertResultsContainFilter('associations', 'X_SELL', $sources);
        $this->assertResultsDoNotContainFilter('associations', 'UPSELL', $sources);
        $this->assertResultsDoNotContainFilter('marketing', 'name', $sources);

        $response = $this->assertCallSuccess(4, 0, null,'erp name');
        $sources = \json_decode($response->getContent(), true);
        $this->assertNotEmpty($sources);

        $this->assertResultsDoNotContainFilter('system', 'family', $sources);
        $this->assertResultsContainFilter('erp', 'erp_name', $sources);
        $this->assertResultsDoNotContainFilter('marketing', 'name', $sources);
    }

    public function test_it_translates_the_labels(): void
    {
        $response = $this->assertCallSuccess(4, 0);
        $sources = \json_decode($response->getContent(), true);
        $this->assertNotEmpty($sources);

        foreach ($sources as $group) {
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

    public function test_it_filters_by_attribute_types(): void
    {
        $response = $this->assertCallSuccess(1000, 0, ['pim_catalog_text']);
        $sources = \json_decode($response->getContent(), true);
        $this->assertNotEmpty($sources);

        foreach ($sources as $group) {
            foreach($group['children'] as $filter) {
                if ($filter['type'] !== 'attribute') {
                    continue;
                }

                $attribute = $this->get('pim_catalog.repository.attribute')->findOneByIdentifier($filter['code']);
                $this->assertNotNull($attribute);
                $this->assertSame('pim_catalog_text', $attribute->getType());
            }
        }
    }

    private function assertCallSuccess(int $limit, int $page, ?array $attributeTypes = null, string $search = null): Response
    {
        $options = [
            'limit' => $limit,
            'page' => $page,
            'attributeTypes' => implode(',', $attributeTypes),
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

    private function assertFiltersCount(int $expectedCount, array $sources): void
    {
        $totalCount = array_reduce($sources, function (int $totalCount, array $group): int {
            return $totalCount + count($group['children']);
        }, 0);

        $this->assertSame($expectedCount, $totalCount);
    }

    private function assertResultsContainFilter(
        string $expectedGroupCode,
        string $expectedFieldCode,
        array $sources
    ): void {
        $this->assertTrue($this->isFilterInResults($expectedGroupCode, $expectedFieldCode, $sources), sprintf(
            'The "%s" field code in "%s" group code is not found in results.',
            $expectedFieldCode,
            $expectedGroupCode,
        ));
    }

    private function assertResultsDoNotContainFilter(
        string $expectedGroupCode,
        string $expectedFieldCode,
        array $sources
    ): void {
        $this->assertFalse($this->isFilterInResults($expectedGroupCode, $expectedFieldCode, $sources), sprintf(
            'The "%s" field code in "%s" group code is found in results.',
            $expectedFieldCode,
            $expectedGroupCode,
        ));
    }

    private function isFilterInResults(
        string $expectedGroupCode,
        string $expectedFieldCode,
        array $sources
    ): bool {
        $found = false;
        foreach ($sources as $group) {
            if ($group['code'] === $expectedGroupCode) {
                foreach ($group['children'] as $filter) {
                    if ($filter['code'] === $expectedFieldCode) {
                        $found = true;

                        break 2;
                    }
                }
            }
        }

        return $found;
    }
}
