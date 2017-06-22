<?php

namespace spec\Pim\Bundle\FilterBundle\Filter\Product;

use Oro\Bundle\FilterBundle\Filter\ChoiceFilter;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Pim\Bundle\FilterBundle\Filter\ProductFilterUtility;
use Pim\Bundle\UserBundle\Context\UserContext;
use Symfony\Component\Form\FormFactoryInterface;

class GroupsFilterSpec extends ObjectBehavior
{
    function let(FormFactoryInterface $factory, ProductFilterUtility $utility, UserContext $userContext)
    {
        $this->beConstructedWith($factory, $utility, $userContext, 'Group');
    }

    function it_is_an_oro_choice_filter()
    {
        $this->shouldBeAnInstanceOf(ChoiceFilter::class);
    }

    function it_applies_a_filter_on_product_groups(
        FilterDatasourceAdapterInterface $datasource,
        $utility
    ) {
        $utility->applyFilter($datasource, 'groups', 'IN', ['foo', 'bar'])->shouldBeCalled();

        $this->apply($datasource, ['type' => null, 'value' => ['foo', 'bar']]);
    }
}
