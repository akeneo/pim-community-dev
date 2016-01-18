<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;

class ProductMassActionRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $em)
    {
        $name = 'Pim\Bundle\CatalogBundle\Model\Product';
        $this->beConstructedWith($em, $name);
    }

    function it_throws_an_exception_when_trying_to_delete_without_product_ids()
    {
        $this->shouldThrow(new \LogicException('No products to remove'))->duringDeleteFromIds(array());
    }
}
