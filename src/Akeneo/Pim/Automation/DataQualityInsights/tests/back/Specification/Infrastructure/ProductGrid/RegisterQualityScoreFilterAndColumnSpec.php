<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\ProductGrid;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Sorter\Configuration as SorterConfiguration;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RegisterQualityScoreFilterAndColumnSpec extends ObjectBehavior
{
    public function let(FeatureFlag $featureFlag, RequestParameters $requestParams)
    {
        $this->beConstructedWith($featureFlag, $requestParams);
    }

    public function it_does_nothing_if_the_grid_is_not_the_product_grid(
        BuildBefore $buildBefore,
        DatagridConfiguration $datagridConfiguration
    ) {
        $datagridConfiguration->getName()->willReturn('random');
        $buildBefore->getConfig()->willReturn($datagridConfiguration);

        $datagridConfiguration->offsetAddToArray(Argument::any())->shouldNotBeCalled();

        $this->buildBefore($buildBefore);
    }

    public function it_does_nothing_if_the_feature_flag_is_on(
        FeatureFlag $featureFlag,
        BuildBefore $buildBefore,
        DatagridConfiguration $datagridConfiguration
    ) {
        $featureFlag->isEnabled()->willReturn(true);
        $datagridConfiguration->getName()->willReturn('product-grid');
        $buildBefore->getConfig()->willReturn($datagridConfiguration);

        $this->buildBefore($buildBefore);
    }

    public function it_unregisters_the_quality_score_filter_and_column_if_feature_flag_is_off(
        BuildBefore $buildBefore,
        DatagridConfiguration $datagridConfiguration,
        $featureFlag
    ) {
        $featureFlag->isEnabled()->willReturn(false);
        $datagridConfiguration->getName()->willReturn('product-grid');
        $buildBefore->getConfig()->willReturn($datagridConfiguration);

        $datagridConfiguration->offsetGet(Configuration::FILTERS_KEY)->willReturn(['columns' => ['filter1' => [], 'data_quality_insights_score' => [], 'filter2' => []]]);
        $datagridConfiguration->offsetAddToArray(Configuration::FILTERS_KEY, ['columns' => ['filter1' => [], 'filter2' => []]])->shouldBeCalled();

        $datagridConfiguration->offsetGet(FormatterConfiguration::COLUMNS_KEY)->willReturn(['column1' => [], 'data_quality_insights_score' => [], 'column2' => []]);
        $datagridConfiguration->offsetUnset(FormatterConfiguration::COLUMNS_KEY)->shouldBeCalled();
        $datagridConfiguration->offsetAddToArray(FormatterConfiguration::COLUMNS_KEY, ['column1' => [], 'column2' => []])->shouldBeCalled();

        $datagridConfiguration->offsetGetByPath(SorterConfiguration::COLUMNS_PATH)->willReturn(['sorter1' => [], 'data_quality_insights_score' => [], 'sorter2' => []]);
        $datagridConfiguration->offsetUnset(SorterConfiguration::COLUMNS_PATH)->shouldBeCalled();
        $datagridConfiguration->offsetSetByPath(SorterConfiguration::COLUMNS_PATH, ['sorter1' => [], 'sorter2' => []])->shouldBeCalled();

        $this->buildBefore($buildBefore);
    }
}
