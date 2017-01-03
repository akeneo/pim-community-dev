<?php

namespace spec\Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\GroupRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;

class ContextConfiguratorSpec extends ObjectBehavior
{
    function let(
        ProductRepositoryInterface $repository,
        AttributeRepositoryInterface $attributeRepository,
        RequestParameters $requestParams,
        UserContext $userContext,
        ObjectManager $objectManager,
        GroupRepositoryInterface $productGroupRepository
    ) {
        $this->beConstructedWith(
            $repository,
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
