<?php

namespace spec\PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Product;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration as FilterConfiguration;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use PimEnterprise\Component\TeamworkAssistant\Repository\ProjectRepositoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class FiltersConfiguratorSpec extends ObjectBehavior
{
    function let(
        ConfiguratorInterface $filtersConfigurator,
        RequestStack $stack,
        ProjectRepositoryInterface $projectRepository,
        TokenStorageInterface $tokenStorage
    ) {
        $this->beConstructedWith($filtersConfigurator, $stack, $projectRepository, $tokenStorage);
    }

    function it_is_a_configurator()
    {
        $this->shouldImplement('Pim\Bundle\DataGridBundle\Datagrid\Configuration\ConfiguratorInterface');
    }
}
