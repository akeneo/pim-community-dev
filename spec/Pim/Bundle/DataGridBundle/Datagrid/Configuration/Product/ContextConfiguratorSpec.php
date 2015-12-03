<?php

namespace spec\Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;

class ContextConfiguratorSpec extends ObjectBehavior
{
    function let(
        DatagridConfiguration $configuration,
        ProductRepositoryInterface $productRepository,
        AttributeRepositoryInterface $attributeRepository,
        RequestParameters $requestParams,
        UserContext $userContext,
        ObjectManager $objectManager,
        GroupRepositoryInterface $productGroupRepository
    ) {
        $this->beConstructedWith(
            $productRepository,
            $attributeRepository,
            $requestParams,
            $userContext,
            $objectManager,
            $productGroupRepository
        );
    }

    function it_is_a_configurator()
    {
        $this->shouldImplement('Pim\Bundle\DataGridBundle\Datagrid\Configuration\ConfiguratorInterface');
    }
}
