<?php

namespace spec\Pim\Bundle\DataGridBundle\Datagrid\Product;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Context\UserContext;
use Prophecy\Argument;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Doctrine\ORM\EntityRepository;

class ContextConfiguratorSpec extends ObjectBehavior
{
    function let(
        DatagridConfiguration $configuration,
        ProductManager $manager,
        RequestParameters $requestParams,
        UserContext $userContext,
        EntityRepository $repository
    ) {
        $this->beConstructedWith($manager, $requestParams, $userContext, $repository);
    }

    function it_is_a_configurator()
    {
        $this->shouldImplement('Pim\Bundle\DataGridBundle\Datagrid\Product\ConfiguratorInterface');
    }
}
