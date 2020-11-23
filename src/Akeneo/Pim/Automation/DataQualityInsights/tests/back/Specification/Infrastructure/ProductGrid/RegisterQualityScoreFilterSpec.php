<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\ProductGrid;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RegisterQualityScoreFilterSpec extends ObjectBehavior
{
    public function let(FeatureFlag $featureFlag)
    {
        $this->beConstructedWith($featureFlag);
    }

    public function it_register_the_quality_score_filter_in_the_datagrid(
        BuildBefore $buildBefore,
        DatagridConfiguration $datagridConfiguration,
        $featureFlag
    ) {
        $datagridConfiguration->getName()->willReturn('product-grid');
        $buildBefore->getConfig()->willReturn($datagridConfiguration);
        $featureFlag->isEnabled()->willReturn(true);

        $datagridConfiguration->offsetGet(Configuration::FILTERS_KEY)->shouldBeCalled();
        $datagridConfiguration->offsetAddToArray(Configuration::FILTERS_KEY, Argument::type('array'))->shouldBeCalled();

        $this->buildBefore($buildBefore);
    }

    public function it_does_not_register_the_enrichment_filter_if_the_grid_is_not_the_product_grid(
        BuildBefore $buildBefore,
        DatagridConfiguration $datagridConfiguration
    ) {
        $datagridConfiguration->getName()->willReturn('random');
        $buildBefore->getConfig()->willReturn($datagridConfiguration);

        $datagridConfiguration->offsetAddToArray(Argument::any())->shouldNotBeCalled();

        $this->buildBefore($buildBefore);
    }

    public function it_does_not_register_the_enrichment_filter_if_DQI_is_not_enabled(
        BuildBefore $buildBefore,
        DatagridConfiguration $datagridConfiguration,
        $featureFlag
    ) {
        $datagridConfiguration->getName()->willReturn('product-grid');
        $buildBefore->getConfig()->willReturn($datagridConfiguration);
        $featureFlag->isEnabled()->willReturn(false);

        $datagridConfiguration->offsetAddToArray(Argument::any())->shouldNotBeCalled();

        $this->buildBefore($buildBefore);
    }
}
