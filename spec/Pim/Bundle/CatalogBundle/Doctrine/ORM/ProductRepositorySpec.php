<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM;

use PhpSpec\ObjectBehavior;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\ProductQueryBuilder;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;

class ProductRepositorySpec extends ObjectBehavior
{
    function let(
        EntityManager $em,
        ProductQueryBuilder $productQB,
        AttributeRepository $attributeRepository
    ) {
        $this->beConstructedWith($em, $productQB, $attributeRepository, 'pim_catalog_product');
    }

    function it_must_implements_product_repository_interface()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface');
    }

    function it_should_throw_an_exception_if_try_to_delete_without_product_ids()
    {
        $this->shouldThrow(new \LogicException('No products to remove'))->duringDeleteFromIds(array());
    }
}
