<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Datagrid\Product;

use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Permission\Bundle\Datagrid\Product\ContextConfigurator;
use Akeneo\Pim\Permission\Bundle\Entity\Repository\AttributeGroupAccessRepository;
use Akeneo\Pim\Permission\Bundle\User\UserContext;
use Akeneo\Pim\Enrichment\Component\Product\Repository\GroupRepositoryInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ContextConfiguratorSpec extends ObjectBehavior
{
    function let(
        ProductRepositoryInterface $productRepository,
        AttributeRepositoryInterface $attributeRepository,
        RequestParameters $requestParams,
        UserContext $userContext,
        ObjectManager $objectManager,
        GroupRepositoryInterface $productGroupRepository,
        RequestStack $requestStack,
        AttributeGroupAccessRepository $accessRepository
    ) {
        $this->beConstructedWith(
            $productRepository,
            $attributeRepository,
            $requestParams,
            $userContext,
            $objectManager,
            $productGroupRepository,
            $requestStack,
            $accessRepository
        );
    }

    function it_is_a_configurator()
    {
        $this->shouldImplement(ConfiguratorInterface::class);
    }

    function it_overrides_base_configurator()
    {
        $this->shouldImplement(ContextConfigurator::class);
    }
}
