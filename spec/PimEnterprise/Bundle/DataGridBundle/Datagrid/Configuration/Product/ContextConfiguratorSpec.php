<?php

namespace spec\PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Product;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration as FilterConfiguration;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\ContextConfigurator;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\GroupRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\AttributeGroupAccessRepository;
use PimEnterprise\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Component\Security\Attributes;

class ContextConfiguratorSpec extends ObjectBehavior
{
    function let(
        ProductRepositoryInterface $productRepository,
        AttributeRepositoryInterface $attributeRepository,
        RequestParameters $requestParams,
        UserContext $userContext,
        ObjectManager $objectManager,
        GroupRepositoryInterface $productGroupRepository,
        AttributeGroupAccessRepository $accessRepository
    ) {
        $this->beConstructedWith(
            $productRepository,
            $attributeRepository,
            $requestParams,
            $userContext,
            $objectManager,
            $productGroupRepository,
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
