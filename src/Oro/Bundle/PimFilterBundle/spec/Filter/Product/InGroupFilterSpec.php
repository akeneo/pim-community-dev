<?php

namespace spec\Oro\Bundle\PimFilterBundle\Filter\Product;

use Oro\Bundle\FilterBundle\Filter\BooleanFilter;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Filter\ObjectCodeResolver;
use Oro\Bundle\PimDataGridBundle\Datagrid\Request\RequestParametersExtractorInterface;
use Oro\Bundle\PimFilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
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
        $this->shouldBeAnInstanceOf(BooleanFilter::class);
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
