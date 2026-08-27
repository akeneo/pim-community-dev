<?php

namespace Oro\Bundle\DataGridBundle\Tests\Unit\EventListener;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\EventListener\FeatureFlagDatagridFilterListener;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Sorter\Configuration as SorterConfiguration;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration as FilterConfiguration;
use PHPUnit\Framework\TestCase;

class FeatureFlagDatagridFilterListenerTest extends TestCase
{
    public function testItKeepsNumericAttributeCodeAsColumnKeyWhenFilteringColumns(): void
    {
        $featureFlags = $this->createMock(FeatureFlags::class);
        $featureFlags->method('isEnabled')->willReturn(false);

        $listener = new FeatureFlagDatagridFilterListener($featureFlags);

        $config = DatagridConfiguration::create([
            FormatterConfiguration::COLUMNS_KEY => [
                'sku' => ['label' => 'Sku'],
                '385' => ['label' => 'Numeric attribute'],
                'hidden' => ['label' => 'Hidden', 'feature_flag' => 'some_flag'],
            ],
            FilterConfiguration::FILTERS_KEY => ['columns' => []],
            'sorters' => ['columns' => []],
        ]);

        $event = new BuildBefore($this->createMock(DatagridInterface::class), $config);

        $listener->filterColumns($event);

        $columns = $config->offsetGet(FormatterConfiguration::COLUMNS_KEY);

        $this->assertArrayHasKey('sku', $columns);
        $this->assertArrayHasKey('385', $columns, 'A numeric attribute code must not be renumbered as a positional array key');
        $this->assertArrayNotHasKey('hidden', $columns);
    }

    public function testNumericColumnKeyStaysConsistentWithTheSorterColumnKeyItIsMatchedAgainst(): void
    {
        $featureFlags = $this->createMock(FeatureFlags::class);
        $featureFlags->method('isEnabled')->willReturn(true);

        $listener = new FeatureFlagDatagridFilterListener($featureFlags);

        $config = DatagridConfiguration::create([
            FormatterConfiguration::COLUMNS_KEY => [
                '385' => ['label' => 'Numeric attribute'],
            ],
            FilterConfiguration::FILTERS_KEY => ['columns' => []],
            'sorters' => [
                'columns' => [
                    '385' => ['data_name' => '385'],
                ],
            ],
        ]);

        $event = new BuildBefore($this->createMock(DatagridInterface::class), $config);

        $listener->filterColumns($event);

        $columns = $config->offsetGet(FormatterConfiguration::COLUMNS_KEY);
        $sorterColumns = $config->offsetGetByPath(SorterConfiguration::COLUMNS_PATH);

        $this->assertSame(array_keys($sorterColumns), array_keys($columns));
    }
}
