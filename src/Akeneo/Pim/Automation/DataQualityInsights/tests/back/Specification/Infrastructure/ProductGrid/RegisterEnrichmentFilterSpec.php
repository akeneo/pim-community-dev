<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\ProductGrid;

use Akeneo\Pim\Automation\DataQualityInsights\Application\FeatureFlag;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\ProductGrid\RegisterEnrichmentFilter;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RegisterEnrichmentFilterSpec extends ObjectBehavior
{
    public function let(FeatureFlag $featureFlag)
    {
        $this->beConstructedWith($featureFlag);
    }

    public function it_register_the_enrichment_filter_in_the_datagrid(
        BuildBefore $buildBefore,
        DatagridConfiguration $datagridConfiguration,
        $featureFlag
    ) {
        $datagridConfiguration->getName()->willReturn(RegisterEnrichmentFilter::PRODUCT_DATAGRID_NAME);
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
        $datagridConfiguration->getName()->willReturn(RegisterEnrichmentFilter::PRODUCT_DATAGRID_NAME);
        $buildBefore->getConfig()->willReturn($datagridConfiguration);
        $featureFlag->isEnabled()->willReturn(false);

        $datagridConfiguration->offsetAddToArray(Argument::any())->shouldNotBeCalled();

        $this->buildBefore($buildBefore);
    }
}
