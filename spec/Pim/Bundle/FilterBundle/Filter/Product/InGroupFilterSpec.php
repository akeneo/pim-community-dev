<?php

namespace spec\Pim\Bundle\FilterBundle\Filter\Product;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactoryInterface;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Pim\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Pim\Bundle\FilterBundle\Filter\ProductFilterUtility;
use Pim\Bundle\CatalogBundle\Model\ProductRepositoryInterface;
use Pim\Bundle\CatalogBundle\Doctrine\ProductQueryBuilderInterface;

class InGroupFilterSpec extends ObjectBehavior
{
    function let(FormFactoryInterface $factory, ProductFilterUtility $utility, RequestParameters $params)
    {
        $this->beConstructedWith($factory, $utility, $params);
    }

    function it_is_an_oro_choice_filter()
    {
        $this->shouldBeAnInstanceOf('Oro\Bundle\FilterBundle\Filter\BooleanFilter');
    }

    function it_applies_a_filter_on_product_when_its_in_an_expected_group(
        FilterDatasourceAdapterInterface $datasource,
        $utility,
        ProductRepositoryInterface $repository,
        ProductQueryBuilderInterface $pqb,
        QueryBuilder $qb,
        RequestParameters $params
    ) {
        $params->get('currentGroup', null)->willReturn(12);
        $datasource->getQueryBuilder()->willReturn($qb);
        $utility->getProductRepository()->willReturn($repository);
        $repository->getProductQueryBuilder($qb)->willReturn($pqb);
        $pqb->addFieldFilter('groups', 'IN', 12)->shouldBeCalled();

        $this->apply($datasource, ['type' => null, 'value' => 1]);
    }
}
