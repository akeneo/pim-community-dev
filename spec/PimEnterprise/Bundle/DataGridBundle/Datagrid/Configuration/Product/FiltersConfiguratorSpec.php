<?php

namespace spec\PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Product;

use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\ProjectRepositoryInterface;
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
        $this->shouldImplement('Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\ConfiguratorInterface');
    }
}
