<?php

namespace spec\Pim\Bundle\CatalogBundle\EventListener\MongoDBODM;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

class SetProductsSubscriberSpec extends ObjectBehavior
{
    function let(ManagerRegistry $registry)
    {
        $this->beConstructedWith(
            $registry,
            'Acme\\Model\\Product',
            ['spec\\Pim\\Bundle\\CatalogBundle\\EventListener\\MongoDBODM\\ProductsAware']
        );
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldBeAnInstanceOf('Doctrine\Common\EventSubscriber');
    }

    function it_subscribes_to_post_load_event()
    {
        $this->getSubscribedEvents()->shouldReturn(['postLoad']);
    }

    function it_set_products_on_product_aware_class_when_group_is_loaded(
        ManagerRegistry $registry,
        ObjectRepository $repository,
        LifecycleEventArgs $args,
        ProductsAware $entity,
        ProductInterface $p1,
        ProductInterface $p2
    ) {
        $args->getEntity()->willReturn($entity);
        $entity->getId()->willReturn(42);
        $registry->getRepository('Acme\\Model\\Product')->willReturn($repository);
        $repository->findBy(['groups' => [42]])->willReturn([$p1, $p2]);

        $entity->setProducts([$p1, $p2])->shouldBeCalled();

        $this->postLoad($args);
    }

    function it_does_not_set_products_on_product_unaware_class_when_group_is_loaded(
        ManagerRegistry $registry,
        ObjectRepository $repository,
        LifecycleEventArgs $args,
        ProductsUnaware $entity,
        ProductInterface $p1,
        ProductInterface $p2
    ) {
        $args->getEntity()->willReturn($entity);

        $entity->setProducts(Argument::any())->shouldNotBeCalled();

        $this->postLoad($args);
    }
}

class ProductsAware
{
    public function getId() {}
    public function setProducts($products) {}
}

class ProductsUnaware
{
    public function setProducts($products) {}
}
