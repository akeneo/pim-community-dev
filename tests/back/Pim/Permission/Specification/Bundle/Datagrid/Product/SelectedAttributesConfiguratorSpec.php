<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Datagrid\Product;

use Akeneo\Pim\Permission\Bundle\Entity\Repository\AttributeGroupAccessRepository;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\RequestStack;

class SelectedAttributesConfiguratorSpec extends ObjectBehavior
{
    function let(
        AttributeRepositoryInterface $attributeRepository,
        UserContext $userContext,
        RequestParameters $requestParams,
        RequestStack $requestStack,
        AttributeGroupAccessRepository $accessRepository
    ) {
        $this->beConstructedWith($attributeRepository, $userContext, $requestParams, $requestStack, $accessRepository);
    }

    function it_is_a_datagrid_configurator()
    {
        $this->shouldImplement(ConfiguratorInterface::class);
    }
}
