<?php

namespace spec\Pim\Bundle\DataGridBundle\Datagrid\Product;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;

class ContextConfiguratorSpec extends ObjectBehavior
{
    function let(DatagridConfiguration $configuration, ProductManager $manager, RequestParameters $requestParams, Request $request, SecurityContextInterface $securityContext)
    {
        $this->beConstructedWith($configuration, $manager, $requestParams, $request, $securityContext);
    }

    function it_is_a_configurator()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\DataGridBundle\Datagrid\Product\ConfiguratorInterface');
    }
}
