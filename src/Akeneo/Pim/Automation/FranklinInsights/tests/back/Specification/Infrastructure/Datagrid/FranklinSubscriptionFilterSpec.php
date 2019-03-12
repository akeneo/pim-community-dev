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

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\FilterInterface;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\Form;

class FranklinSubscriptionFilterSpec extends ObjectBehavior
{
    public function let(FilterInterface $baseFilter, ProductFilterUtility $filterUtility): void
    {
        $this->beConstructedWith($baseFilter, $filterUtility);
    }

    public function it_is_a_filter(): void
    {
        $this->shouldImplement(FilterInterface::class);
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

    public function it_initializes_the_filter(FilterInterface $baseFilter): void
    {
        $baseFilter->init('franklin_subscription', ['type' => 'franklin_subscription'])->shouldBeCalled();

        $this->init('franklin_subscription', ['type' => 'franklin_subscription']);
    }

    public function it_returns_the_name_of_the_filter(FilterInterface $baseFilter): void
    {
        $baseFilter->getName()->willReturn('franklin_subscription');

        $this->getName()->shouldReturn('franklin_subscription');
    }

    public function it_returns_the_form_of_the_filter(FilterInterface $baseFilter, Form $form): void
    {
        $baseFilter->getForm()->willReturn($form);

        $this->getForm()->shouldReturn($form);
    }

    public function it_returns_the_metadata_of_the_filter(FilterInterface $baseFilter): void
    {
        $baseFilter->getMetadata()->willReturn(['name' => 'franklin_subscription']);

        $this->getMetadata()->shouldReturn(['name' => 'franklin_subscription']);
    }
}
