<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid;

use Oro\Bundle\PimDataGridBundle\Extension\MassAction\MassActionDispatcher;
use PhpSpec\ObjectBehavior;

class OroToPimGridFilterAdapterSpec extends ObjectBehavior
{
    function let(MassActionDispatcher $massActionDispatcher)
    {
        $this->beConstructedWith($massActionDispatcher);
    }

    function it_adapts_fiters_for_the_proposal_grid($massActionDispatcher)
    {
        $massActionDispatcher->dispatch(['gridName' => 'proposal-grid','oro grid parameters'])->willReturn(['pim grid parameters']);

        $this->adapt(['gridName' => 'proposal-grid','oro grid parameters'])->shouldReturn(['values' => ['pim grid parameters']]);
    }

    function it_adapts_fiters_for_the_published_product_grid($massActionDispatcher)
    {
        $massActionDispatcher->getRawFilters(['gridName' => 'published-product-grid','oro grid parameters'])->willReturn(['pim grid parameters']);

        $this->adapt(['gridName' => 'published-product-grid','oro grid parameters'])->shouldReturn(['pim grid parameters']);
    }

    function it_adds_completeness_filter_if_it_is_for_a_project_view($massActionDispatcher)
    {
        $parameters = [
            'dataLocale' => 'en_US',
            'dataScope' => ['value' => 'ecommerce'],
            'gridName' => 'product-grid',
            'filters' => [
                'project_completeness' => [
                    'value' => 5
                ]
            ]
        ];

        $massActionRawFilters = [
            [
                'field' => 'sku',
                'operator' => 'CONTAINS',
                'value' => 'DP',
            ],
            [
                'field' => 'categories.id',
                'operator' => 'IN',
                'value' => [12, 13, 14],
            ]
        ];

        $massActionDispatcher->getRawFilters($parameters)->willReturn($massActionRawFilters);

        $this->adapt($parameters)->shouldReturn(array_merge($massActionRawFilters, [
            [
                'field'    => 'completeness',
                'operator' => '>',
                'value'    => 0,
                'context'  => [
                    'locale' => 'en_US',
                    'scope'  => 'ecommerce'
                ]
            ],
            [
                'field'    => 'completeness',
                'operator' => '<',
                'value'    => 100,
                'context'  => [
                    'locale' => 'en_US',
                    'scope'  => 'ecommerce'
                ]
            ]
        ]));
    }
}
