<?php

namespace spec\PimEnterprise\Bundle\CatalogBundle\Persistence\ORM;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ManagerRegistry;


class EntityChangesProviderSpec extends ObjectBehavior
{
    function let(ManagerRegistry $registry)
    {
        $this->beConstructedWith($registry);
    }

    function it_computes_new_values_through_the_unit_of_work(
        $registry,
        ProductInterface $product,
        AbstractProductValue $sku,
        AbstractProductValue $name,
        AbstractProductValue $description,
        EntityManager $manager,
        UnitOfWork $uow,
        ClassMetadata $metadata
    ) {
        $registry->getManagerForClass(get_class($product->getWrappedObject()))->willReturn($manager);
        $manager->getUnitOfWork()->willReturn($uow);
        $manager->getClassMetadata(Argument::any())->willReturn($metadata);

        $product->getValues()->willReturn([$sku, $name, $description]);
        $uow->computeChangeSet($metadata, $sku)->shouldBeCalled();
        $uow->computeChangeSet($metadata, $name)->shouldBeCalled();
        $uow->computeChangeSet($metadata, $description)->shouldBeCalled();

        $uow->getEntityChangeSet($sku)->willReturn([]);
        $uow->getEntityChangeSet($name)->willReturn(['varchar' => ['foo', 'bar']]);
        $uow->getEntityChangeSet($description)->willReturn(['text' => ['long test is long', 'long text is long']]);

        $sku->getId()->shouldNotBeCalled();
        $name->getId()->willReturn(1);
        $description->getId()->willReturn(2);

        $this->computeNewValues($product)->shouldReturn(
            [
                1 => ['varchar' => 'bar'],
                2 => ['text' => 'long text is long'],
            ]
        );
    }
}
