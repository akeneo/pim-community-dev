<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\EventListener\Datagrid;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use PhpSpec\ObjectBehavior;

class ConfigureAttributeGridListenerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\EventListener\Datagrid\ConfigureAttributeGridListener');
    }

    function it_adds_a_smart_column_and_filter_to_the_grid(BuildBefore $event, DatagridConfiguration $config)
    {
        $event->getConfig()->willReturn($config);

        $config->offsetAddToArrayByPath(
            '[columns]',
            [
                'smart' => [
                    'label'         => 'pimee_catalog_rule.attribute.grid.is_smart.label',
                    'frontend_type' => 'boolean-status',
                    'data_name'     => 'is_smart'
                ]
            ]
        )->shouldBeCalled();

        $config->offsetAddToArrayByPath(
            '[filters][columns]',
            [
                'smart' => [
                    'type'      => 'attribute_is_smart',
                    'data_name' => 'is_smart'
                ]
            ]
        )->shouldBeCalled();

        $this->buildBefore($event);
    }
}
