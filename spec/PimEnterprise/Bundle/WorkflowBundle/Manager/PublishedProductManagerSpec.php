<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Event\PublishedProductEvents;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Publisher\PublisherInterface;
use PimEnterprise\Bundle\WorkflowBundle\Publisher\UnpublisherInterface;
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
        EventDispatcherInterface $eventDispatcher,
        PublisherInterface $publisher,
        UnpublisherInterface $unpublisher
    )
    {
        $this->beConstructedWith($manager, $repository, $eventDispatcher, $publisher, $unpublisher);
    }

    function it_publishes_a_product(
        $eventDispatcher,
        $publisher,
        $manager,
        $repository,
        ObjectManager $om,
        AbstractProduct $product,
        PublishedProductInterface $published
    ) {
        $repository->findOneByOriginalProduct(Argument::any())->willReturn(null);
        $manager->getObjectManager()->willReturn($om);
        $publisher->publish($product)->willReturn($published);

        $eventDispatcher->dispatch(PublishedProductEvents::PRE_PUBLISH, Argument::any(), null)->shouldBeCalled();
        $eventDispatcher->dispatch(PublishedProductEvents::POST_PUBLISH, Argument::cetera())->shouldBeCalled();

        $om->persist($published)->shouldBeCalled();
        $om->flush()->shouldBeCalled();

        $this->publish($product);
    }

    function it_publishes_a_product_already_published(
        $eventDispatcher,
        $publisher,
        $unpublisher,
        $manager,
        $repository,
        ObjectManager $om,
        AbstractProduct $product,
        PublishedProductInterface $alreadyPublished,
        PublishedProductInterface $published
    ) {
        $repository->findOneByOriginalProduct(Argument::any())->willReturn($alreadyPublished);
        $manager->getObjectManager()->willReturn($om);
        $publisher->publish($product)->willReturn($published);

        $eventDispatcher->dispatch(PublishedProductEvents::PRE_PUBLISH, Argument::any(), null)->shouldBeCalled();
        $eventDispatcher->dispatch(PublishedProductEvents::POST_PUBLISH, Argument::cetera())->shouldBeCalled();

        $unpublisher->unpublish($alreadyPublished)->shouldBeCalled();
        $om->remove($alreadyPublished)->shouldBeCalled();
        $om->persist($published)->shouldBeCalled();
        $om->flush()->shouldBeCalled();

        $this->publish($product);
    }

    function it_unpublish_a_product(
        $eventDispatcher,
        $unpublisher,
        $manager,
        ObjectManager $om,
        PublishedProductInterface $published,
        ProductInterface $product
    ) {
        $manager->getObjectManager()->willReturn($om);
        $published->getOriginalProduct()->willReturn($product);
        $unpublisher->unpublish($published)->shouldBeCalled();

        $eventDispatcher->dispatch(PublishedProductEvents::PRE_UNPUBLISH, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(PublishedProductEvents::POST_UNPUBLISH, Argument::any(), null)->shouldBeCalled();

        $om->remove($published)->shouldBeCalled();
        $om->flush()->shouldBeCalled();

        $this->unpublish($published);
    }
}
