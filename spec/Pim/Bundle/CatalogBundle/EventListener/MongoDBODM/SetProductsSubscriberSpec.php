<?php

namespace spec\Pim\Bundle\CatalogBundle\EventListener\MongoDBODM;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

class SetProductsSubscriberSpec extends ObjectBehavior
{
    function let(ManagerRegistry $registry)
    {
        $this->beConstructedWith(
            $registry,
            'Acme\\Model\\Product',
            [
                [
                    'class' => 'spec\\Pim\\Bundle\\CatalogBundle\\EventListener\\MongoDBODM\\ProductsAware',
                    'property' => 'foo',
                ],
                [
                    'class' => 'spec\\Pim\\Bundle\\CatalogBundle\\EventListener\\MongoDBODM\\InvalidProductsAware',
                    'property' => 'bar',
                ],
            ]
        );
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldBeAnInstanceOf('Doctrine\Common\EventSubscriber');
    }

    function it_subscribes_to_doctrine_events()
    {
        $this->getSubscribedEvents()->shouldReturn(['postLoad']);
    }

    function it_sets_products_on_product_aware_class_when_group_is_loaded(
        ManagerRegistry $registry,
        EntityManager $em,
        LifecycleEventArgs $args,
        ClassMetadata $metadata,
        \ReflectionClass $reflClass,
        \ReflectionProperty $reflProp,
        ProductsAware $entity
    ) {
        $args->getEntity()->willReturn($entity);
        $args->getEntityManager()->willReturn($em);
        $em->getClassMetadata(Argument::type('string'))->willReturn($metadata);
        $metadata->reflClass = $reflClass;
        $reflClass->hasProperty('products')->willReturn(true);
        $reflClass->getProperty('products')->willReturn($reflProp);

        $reflProp->setAccessible(true)->shouldBeCalled();
        $reflProp->setValue($entity, Argument::type('Gedmo\\References\\LazyCollection'))->shouldBeCalled();

        $this->postLoad($args);
    }

    function it_does_not_set_products_on_product_unaware_class_when_group_is_loaded(
        ManagerRegistry $registry,
        LifecycleEventArgs $args,
        ProductsUnaware $entity
    ) {
        $args->getEntity()->willReturn($entity);

        $entity->setProducts(Argument::any())->shouldNotBeCalled();

        $this->postLoad($args);
    }

    function it_throws_exception_when_setProducts_method_does_not_exist(
        ManagerRegistry $registry,
        EntityManager $em,
        LifecycleEventArgs $args,
        ClassMetadata $metadata,
        \ReflectionClass $reflClass,
        LifecycleEventArgs $args,
        InvalidProductsAware $entity
    ) {
        $args->getEntity()->willReturn($entity);
        $args->getEntityManager()->willReturn($em);
        $em->getClassMetadata(Argument::type('string'))->willReturn($metadata);
        $metadata->reflClass = $reflClass;
        $reflClass->hasProperty('products')->willReturn(false);

        $this->shouldThrow('\LogicException')->duringPostLoad($args);
    }
}

class ProductsAware
{
    public function getId() {}
    public function setProducts($products) {}
}

class InvalidProductsAware
{
}

class ProductsUnaware
{
    public function setProducts($products) {}
}
