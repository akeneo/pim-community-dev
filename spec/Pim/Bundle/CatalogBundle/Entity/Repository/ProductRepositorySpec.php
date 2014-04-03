<?php

namespace spec\Pim\Bundle\CatalogBundle\Entity\Repository;

use PhpSpec\ObjectBehavior;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;

class ProductRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $em, ClassMetadata $class)
    {
        $class->name = 'Pim\Bundle\CatalogBundle\Model\Product';
        $this->beConstructedWith($em, $class);
    }

    function it_must_implements_product_repository_interface()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Model\ProductRepositoryInterface');
    }

    function it_should_throw_an_exception_if_try_to_delete_without_product_ids()
    {
        $this->shouldThrow(new \LogicException('No products to remove'))->duringDeleteFromIds(array());
    }
}
