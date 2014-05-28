<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM;

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

    function it_is_a_product_repository()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface');
    }
}
