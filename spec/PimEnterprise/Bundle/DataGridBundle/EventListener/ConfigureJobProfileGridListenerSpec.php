<?php

namespace spec\PimEnterprise\Bundle\DataGridBundle\EventListener;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Extension\Action\ActionExtension;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;

class ConfigureJobProfileGridListenerSpec extends ObjectBehavior
{
    function let(SecurityContextInterface $security)
    {
        $this->beConstructedWith($security);
    }

    function its_build_before_method_registers_row_action_configuration_closure(
        BuildBefore $event,
        DatagridConfiguration $config
    ) {
        $event->getConfig()->willReturn($config);

        $config
            ->offsetSetByPath(
                sprintf('[%s]', ActionExtension::ACTION_CONFIGURATION_KEY),
                Argument::type('\Closure')
            )
            ->shouldBeCalled();

        $this->onBuildBefore($event);
    }
}
