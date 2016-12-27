<?php

namespace spec\Pim\Bundle\FilterBundle\Filter\Product;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\ObjectCodeResolver;
use Pim\Bundle\DataGridBundle\Datagrid\Request\RequestParametersExtractorInterface;
use Pim\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Pim\Bundle\FilterBundle\Filter\ProductFilterUtility;
use Symfony\Component\Form\FormFactoryInterface;

class InGroupFilterSpec extends ObjectBehavior
{
    function let(
        FormFactoryInterface $factory,
        ProductFilterUtility $utility,
        RequestParametersExtractorInterface $extractor,
        ObjectCodeResolver $codeResolver
    ) {
        $this->beConstructedWith($factory, $utility, $extractor, $codeResolver);
    }

    function it_is_an_oro_choice_filter()
    {
        $this->shouldBeAnInstanceOf('Oro\Bundle\FilterBundle\Filter\BooleanFilter');
    }

    function it_applies_a_filter_on_product_when_its_in_an_expected_group(
        $utility,
        $codeResolver,
        FilterDatasourceAdapterInterface $datasource,
        RequestParametersExtractorInterface $extractor
    ) {
        $extractor->getDatagridParameter('currentGroup')->willReturn(12);
        $codeResolver->getCodesFromIds('group', [12])->willReturn(['foo']);

        $utility->applyFilter($datasource, 'groups', 'IN', ['foo'])->shouldBeCalled();

        $this->apply($datasource, ['type' => null, 'value' => 1]);
    }
}
