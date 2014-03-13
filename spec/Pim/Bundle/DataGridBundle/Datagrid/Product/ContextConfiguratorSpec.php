<?php

namespace spec\Pim\Bundle\DataGridBundle\Datagrid\Product;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Pim\Bundle\DataGridBundle\Manager\DatagridViewManager;
use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;

class ContextConfiguratorSpec extends ObjectBehavior
{
    function let(DatagridConfiguration $configuration, FlexibleManager $manager, RequestParameters $requestParams, Request $request, SecurityContextInterface $securityContext, DatagridViewManager $viewManager)
    {
        $this->beConstructedWith($configuration, $manager, $requestParams, $request, $securityContext, $viewManager);
    }

    function it_is_a_configurator()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\DataGridBundle\Datagrid\Product\ConfiguratorInterface');
    }
}
