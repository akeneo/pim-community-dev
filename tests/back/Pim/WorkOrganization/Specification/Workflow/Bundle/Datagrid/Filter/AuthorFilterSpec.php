<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Filter;

use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimFilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Filter\AuthorFilter;
use Symfony\Component\Form\FormFactoryInterface;

class AuthorFilterSpec extends ObjectBehavior
{
    function let(FormFactoryInterface $factory, FilterUtility $util, FieldFilterInterface $authorFilter)
    {
        $this->beConstructedWith($factory, $util, $authorFilter);

        $this->init(
            'foo',
            [
                ProductFilterUtility::DATA_NAME_KEY => 'data_name_key',
            ]
        );
    }

    function it_initializes_filter_with_name()
    {
        $this->getName()->shouldReturn('foo');
    }

    function it_is_an_author_filter()
    {
        $this->shouldBeAnInstanceOf(AuthorFilter::class);
    }

    function it_applies_an_author_filter(
        FilterDatasourceAdapterInterface $datasource
    ) {

        $this->apply(
            $datasource,
            [
                'value' => [1, 2],
                'type' => '='
            ]
        )->shouldReturn(true);
    }
}
