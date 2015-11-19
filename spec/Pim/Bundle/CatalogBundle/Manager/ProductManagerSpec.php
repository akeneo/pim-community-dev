<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;

class ProductManagerSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        ProductRepositoryInterface $productRepository,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith(
            $objectManager,
            $productRepository,
            $attributeRepository
        );
    }

    function it_is_a_product_manager()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Manager\ProductManagerInterface');
    }

    function it_has_a_product_repository(ProductRepositoryInterface $productRepository)
    {
        $this->getProductRepository()->shouldReturn($productRepository);
    }

    function it_checks_value_existence(ProductRepositoryInterface $productRepository, ProductValueInterface $value)
    {
        $productRepository->valueExists($value)->willReturn(true);
        $this->valueExists($value)->shouldReturn(true);

        $productRepository->valueExists($value)->willReturn(false);
        $this->valueExists($value)->shouldReturn(false);
    }
}
