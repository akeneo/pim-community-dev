<?php

namespace spec\Pim\Bundle\CatalogBundle\EventListener\MongoDBODM;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

class SetGroupProductsSubscriberSpec extends ObjectBehavior
{
    function let(ManagerRegistry $registry)
    {
        $this->beConstructedWith($registry, 'Foo\\Bar');
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldBeAnInstanceOf('Doctrine\Common\EventSubscriber');
    }

    function it_subscribes_to_post_load_event()
    {
        $this->getSubscribedEvents()->shouldReturn(['postLoad']);
    }

    function it_set_products_when_group_is_loaded(
        ManagerRegistry $registry,
        ObjectRepository $repository,
        LifecycleEventArgs $args,
        Group $group,
        ProductInterface $p1,
        ProductInterface $p2
    ) {
        $args->getEntity()->willReturn($group);
        $group->getId()->willReturn(42);
        $registry->getRepository('Foo\\Bar')->willReturn($repository);
        $repository->findBy(['groups' => [42]])->willReturn([$p1, $p2]);

        $group->setProducts([$p1, $p2])->shouldBeCalled();

        $this->postLoad($args);
    }
}
