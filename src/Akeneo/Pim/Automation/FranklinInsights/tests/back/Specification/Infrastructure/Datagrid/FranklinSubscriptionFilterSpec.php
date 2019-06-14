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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Datagrid;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Datagrid\FranklinSubscriptionFilter;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\ChoiceFilter;
use Oro\Bundle\FilterBundle\Filter\FilterInterface;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactoryInterface;

class FranklinSubscriptionFilterSpec extends ObjectBehavior
{
    public function let(FormFactoryInterface $formFactory, ProductFilterUtility $filterUtility): void
    {
        $this->beConstructedWith($formFactory, $filterUtility);
    }

    public function it_is_a_choice_filter(): void
    {
        $this->shouldImplement(FilterInterface::class);
        $this->shouldHaveType(ChoiceFilter::class);
    }

    public function it_is_the_franklin_subscription_filter()
    {
        $this->shouldHaveType(FranklinSubscriptionFilter::class);
    }

    public function it_applies_a_franklin_subscription_filter(
        ProductFilterUtility $filterUtility,
        FilterDatasourceAdapterInterface $filterDatasource
    ): void {
        $filterUtility
            ->applyFilter($filterDatasource, 'franklin_subscription', '=', true)
            ->shouldBeCalled();

        $this->apply($filterDatasource, ['value' => 1])->shouldReturn(true);
    }

    public function it_does_not_apply_the_filter_if_there_is_no_value(
        ProductFilterUtility $filterUtility,
        FilterDatasourceAdapterInterface $filterDatasource
    ): void {
        $filterUtility->applyFilter(Argument::cetera())->shouldNotBeCalled();

        $this->apply($filterDatasource, ['value' => null])->shouldReturn(false);
        $this->apply($filterDatasource, ['foo' => 1])->shouldReturn(false);
    }
}
