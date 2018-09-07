<?php

namespace spec\PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Product;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\GroupRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Product\ContextConfigurator;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\AttributeGroupAccessRepository;
use PimEnterprise\Bundle\UserBundle\Context\UserContext;
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
