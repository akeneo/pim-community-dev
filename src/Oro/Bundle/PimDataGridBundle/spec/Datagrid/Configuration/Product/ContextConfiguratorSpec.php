<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product;

use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use PhpSpec\ObjectBehavior;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\GroupRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ContextConfiguratorSpec extends ObjectBehavior
{
    function let(
        ProductRepositoryInterface $repository,
        RequestParameters $requestParams,
        UserContext $userContext,
        ObjectManager $objectManager,
        GroupRepositoryInterface $productGroupRepository,
        RequestStack $requestStack
    ) {
        $this->beConstructedWith(
            $repository,
            $requestParams,
            $userContext,
            $objectManager,
            $productGroupRepository,
            $requestStack
        );
    }

    function it_is_a_configurator()
    {
        $this->shouldImplement(ConfiguratorInterface::class);
    }
}
