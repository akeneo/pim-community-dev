<?php

namespace spec\Akeneo\Bundle\StorageUtilsBundle\Doctrine;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use PhpSpec\ObjectBehavior;
use Doctrine\ORM\Query\Expr;
use Pim\Bundle\CatalogBundle\Model\Product;
use Prophecy\Argument;

class ObjectDetacherSpec extends ObjectBehavior
{
    function let(ManagerRegistry $registry)
    {
        $this->beConstructedWith($registry);
    }

    function it_detaches_an_object_from_object_manager($registry, ObjectManager $manager)
    {
        $product = new Product();
        $registry->getManagerForClass(ClassUtils::getClass($product))
            ->shouldBeCalled()
            ->willReturn($manager);

        $manager->detach($product)->shouldBeCalled();

        $this->detach($product);
    }
}
