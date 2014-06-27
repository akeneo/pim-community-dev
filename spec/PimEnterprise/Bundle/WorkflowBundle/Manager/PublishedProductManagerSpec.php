<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use PimEnterprise\Bundle\WorkflowBundle\Event\PublishedProductEvents;
use PimEnterprise\Bundle\WorkflowBundle\Factory\PublishedProductFactory;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedProductRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PublishedProductManagerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\WorkflowBundle\Manager\PublishedProductManager');
    }

    function let(
        ProductManager $manager,
        PublishedProductRepositoryInterface $repository,
        PublishedProductFactory $factory,
        EventDispatcherInterface $eventDispatcher
    )
    {
        $this->beConstructedWith($manager, $repository, $factory, $eventDispatcher);
    }

    function it_publishes_a_product(
        $eventDispatcher,
        $factory,
        $manager,
        $repository,
        ObjectManager $om,
        AbstractProduct $product,
        PublishedProductInterface $published
    ) {
        $repository->findOneByOriginalProductId(Argument::any())->willReturn(null);
        $manager->getObjectManager()->willReturn($om);
        $factory->createPublishedProduct($product)->willReturn($published);

        $eventDispatcher->dispatch(PublishedProductEvents::PRE_PUBLISH, Argument::any())->shouldBeCalled();
        $eventDispatcher->dispatch(PublishedProductEvents::POST_PUBLISH, Argument::any())->shouldBeCalled();

        $om->persist($published)->shouldBeCalled();
        $om->flush()->shouldBeCalled();

        $this->publish($product);
    }

    function it_publishes_a_product_already_published(
        $eventDispatcher,
        $factory,
        $manager,
        $repository,
        ObjectManager $om,
        AbstractProduct $product,
        PublishedProductInterface $alreadyPublished,
        PublishedProductInterface $published
    ) {
        $repository->findOneByOriginalProductId(Argument::any())->willReturn($alreadyPublished);
        $manager->getObjectManager()->willReturn($om);
        $factory->createPublishedProduct($product)->willReturn($published);

        $eventDispatcher->dispatch(PublishedProductEvents::PRE_PUBLISH, Argument::any())->shouldBeCalled();
        $eventDispatcher->dispatch(PublishedProductEvents::POST_PUBLISH, Argument::any())->shouldBeCalled();

        $om->remove($alreadyPublished)->shouldBeCalled();
        $om->persist($published)->shouldBeCalled();
        $om->flush()->shouldBeCalled();

        $this->publish($product);
    }
}
