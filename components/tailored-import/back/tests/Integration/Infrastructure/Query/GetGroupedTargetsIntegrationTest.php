<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this target code.
 */

namespace Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Query;

use Akeneo\Platform\TailoredImport\Application\GetGroupedTargets\GetGroupedTargetsInterface;
use Akeneo\Platform\TailoredImport\Application\GetGroupedTargets\GetGroupedTargetsQuery;
use Akeneo\Platform\TailoredImport\Application\GetGroupedTargets\GroupedTargetsResult;
use Akeneo\Platform\TailoredImport\Test\Integration\IntegrationTestCase;
use Akeneo\Test\Integration\Configuration;

final class GetGroupedTargetsIntegrationTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->logAs('julia');
    }

    public function test_it_returns_the_first_page_of_available_targets(): void
    {
        $targetGroups = $this->getNormalizedGroupedTargets(4, ['attribute' => 0, 'system' => 0]);

        $this->assertNotEmpty($targetGroups);
        $this->assertArrayHasKey('code', $targetGroups[0]);
        $this->assertArrayHasKey('label', $targetGroups[0]);
        $this->assertArrayHasKey('children', $targetGroups[0]);

        $this->assertSame('system', $targetGroups[0]['code']);
        $this->assertSame('System', $targetGroups[0]['label']);

        $target = $targetGroups[0]['children'][0];
        $this->assertArrayHasKey('code', $target);
        $this->assertArrayHasKey('label', $target);
        $this->assertArrayHasKey('type', $target);

        $this->assertSame('categories', $target['code']);
        $this->assertSame('property', $target['type']);
        $this->assertSame('Categories', $target['label']);

        $this->assertFiltersCount(4, $targetGroups);
    }

    public function test_it_returns_system_filters_and_attribute_filters(): void
    {
        $targetGroups = $this->getNormalizedGroupedTargets(100, ['attribute' => 0, 'system' => 0]);
        $this->assertNotEmpty($targetGroups);
        $this->assertGreaterThan(1, count($targetGroups));

        $this->assertSame('system', $targetGroups[0]['code']);
        $this->assertSame('System', $targetGroups[0]['label']);
        $this->assertSame('erp', $targetGroups[1]['code']);
        $this->assertSame('ERP', $targetGroups[1]['label']);

        $this->assertTargetGroupContainTarget('system', 'family', $targetGroups);
        $this->assertTargetGroupContainTarget('erp', 'ean', $targetGroups);
        $this->assertTargetGroupContainTarget('erp', 'erp_name', $targetGroups);
        $this->assertTargetGroupContainTarget('marketing', 'name', $targetGroups);
    }

    public function test_it_paginates_the_results(): void
    {
        $targetGroups = $this->getNormalizedGroupedTargets(4, ['attribute' => 0, 'system' => 10]);
        $this->assertNotEmpty($targetGroups);

        $this->assertTargetGroupDoNotContainTarget('system', 'family', $targetGroups);

        $targetGroups = $this->getNormalizedGroupedTargets(10, ['attribute' => 1000, 'system' => 1000]);
        $this->assertEmpty($targetGroups);
    }

    public function test_it_can_search_by_text(): void
    {
        $targetGroups = $this->getNormalizedGroupedTargets(6, ['attribute' => 0, 'system' => 0], 'am');
        $this->assertNotEmpty($targetGroups);

        $this->assertTargetGroupDoNotContainTarget('system', 'categories', $targetGroups);
        $this->assertTargetGroupContainTarget('system', 'family', $targetGroups);
        $this->assertTargetGroupContainTarget('erp', 'erp_name', $targetGroups);
        $this->assertTargetGroupContainTarget('marketing', 'name', $targetGroups);
        $this->assertTargetGroupContainTarget('marketing', 'variation_name', $targetGroups);

        $targetGroups = $this->getNormalizedGroupedTargets(4, ['attribute' => 0, 'system' => 0], 'erp name');
        $this->assertNotEmpty($targetGroups);

        $this->assertTargetGroupDoNotContainTarget('system', 'family', $targetGroups);
        $this->assertTargetGroupContainTarget('erp', 'erp_name', $targetGroups);
        $this->assertTargetGroupDoNotContainTarget('marketing', 'name', $targetGroups);
    }

    public function test_it_translates_the_labels(): void
    {
        $targetGroups = $this->getNormalizedGroupedTargets(4, ['attribute' => 0, 'system' => 0]);
        $this->assertNotEmpty($targetGroups);

        foreach ($targetGroups as $group) {
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

    private function getNormalizedGroupedTargets(int $limit, array $offset, string $search = null): array
    {
        return $this->getGroupedTargets($limit, $offset, $search)->normalize()['results'];
    }

    private function getGroupedTargets(int $limit, array $offset, string $search = null): GroupedTargetsResult
    {
        $queryObject = new GetGroupedTargetsQuery();
        $queryObject->limit = $limit;
        $queryObject->attributeOffset = $offset['attribute'];
        $queryObject->systemOffset = $offset['system'];
        $queryObject->search = $search;

        return $this->getQuery()->get($queryObject);
    }

    private function assertFiltersCount(int $expectedCount, array $targetGroups): void
    {
        $totalCount = array_reduce($targetGroups, static fn (int $totalCount, array $group): int => $totalCount + count($group['children']), 0);

        $this->assertSame($expectedCount, $totalCount);
    }

    private function assertTargetGroupContainTarget(
        string $expectedTargetGroupCode,
        string $expectedTargetCode,
        array $targetGroups
    ): void {
        $this->assertTrue($this->isTargetInTargetGroup($expectedTargetGroupCode, $expectedTargetCode, $targetGroups), sprintf(
            'The "%s" target code in "%s" target group code is not found in results.',
            $expectedTargetCode,
            $expectedTargetGroupCode,
        ));
    }

    private function assertTargetGroupDoNotContainTarget(
        string $expectedTargetGroupCode,
        string $expectedTargetCode,
        array $targetGroups
    ): void {
        $this->assertFalse($this->isTargetInTargetGroup($expectedTargetGroupCode, $expectedTargetCode, $targetGroups), sprintf(
            'The "%s" target code in "%s" target group code is found in results.',
            $expectedTargetCode,
            $expectedTargetGroupCode,
        ));
    }

    private function isTargetInTargetGroup(
        string $expectedTargetGroupCode,
        string $expectedTargetCode,
        array $targetGroups
    ): bool {
        foreach ($targetGroups as $group) {
            if ($group['code'] !== $expectedTargetGroupCode) {
                continue;
            }

            foreach ($group['children'] as $filter) {
                if ($filter['code'] === $expectedTargetCode) {
                    return true;
                }
            }
        }

        return false;
    }

    private function getQuery(): GetGroupedTargetsInterface
    {
        return $this->get('akeneo.tailored_import.query.get_grouped_targets');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
