<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\AssociationInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
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
    ) {
        $this->beConstructedWith(
            $manager,
            $repository,
            $eventDispatcher,
            $publisher,
            $unpublisher
        );
    }

    function it_is_a_configurable_step_element()
    {
        $this->beAnInstanceOf('Pim\Bundle\CatalogBundle\Manager\ProductManagerInterface');
    }

    function it_publishes_a_product(
        $eventDispatcher,
        $publisher,
        $manager,
        $repository,
        ObjectManager $om,
        ProductInterface $product,
        PublishedProductInterface $published
    ) {
        $repository->findOneByOriginalProduct(Argument::any())->willReturn(null);
        $manager->getObjectManager()->willReturn($om);
        $publisher->publish($product, [])->willReturn($published);

        $eventDispatcher->dispatch(PublishedProductEvents::PRE_PUBLISH, Argument::any(), null)->shouldBeCalled();
        $eventDispatcher->dispatch(PublishedProductEvents::POST_PUBLISH, Argument::cetera())->shouldBeCalled();

        $om->persist($published)->shouldBeCalled();
        $om->flush()->shouldBeCalled();

        $this->publish($product);
    }

    function it_publishes_products(
        $publisher,
        $repository,
        $manager,
        ProductInterface $productFoo,
        ProductInterface $productBar,
        ObjectManager $om,
        PublishedProductInterface $publishedFoo,
        PublishedProductInterface $publishedBar,
        AssociationInterface $association
    ) {
        $repository->findOneByOriginalProduct($productBar)->willReturn($publishedFoo);
        $repository->findOneByOriginalProduct($productFoo)->willReturn($publishedBar);

        $manager->getObjectManager()->willReturn($om);

        $productFoo->getAssociations()->willReturn([$association]);
        $productBar->getAssociations()->willReturn([$association]);

        $publisher->publish($association, ['published' => $publishedFoo])->willReturn($association);
        $publisher->publish($association, ['published' => $publishedBar])->willReturn($association);

        $publisher->publish($productFoo, ['with_associations' => false, 'flush' => false])->shouldBeCalledTimes(
            1
        )->willReturn(
            $publishedFoo
        );
        $publisher->publish($productBar, ['with_associations' => false, 'flush' => false])->shouldBeCalledTimes(
            1
        )->willReturn(
            $publishedBar
        );

        $om->remove(Argument::any())->shouldBeCalled();
        $om->flush()->shouldBeCalled();

        $om->persist($publishedFoo)->shouldBeCalledTimes(1);
        $om->persist($publishedBar)->shouldBeCalledTimes(1);

        $om->flush()->shouldBeCalledTimes(4);

        $this->publishAll([$productFoo, $productBar]);
    }

    function it_publishes_a_product_already_published(
        $eventDispatcher,
        $publisher,
        $unpublisher,
        $manager,
        $repository,
        ObjectManager $om,
        ProductInterface $product,
        PublishedProductInterface $alreadyPublished,
        PublishedProductInterface $published
    ) {
        $repository->findOneByOriginalProduct(Argument::any())->willReturn($alreadyPublished);
        $manager->getObjectManager()->willReturn($om);
        $publisher->publish($product, [])->willReturn($published);

        $eventDispatcher->dispatch(PublishedProductEvents::PRE_PUBLISH, Argument::any(), null)->shouldBeCalled();
        $eventDispatcher->dispatch(PublishedProductEvents::POST_PUBLISH, Argument::cetera())->shouldBeCalled();

        $unpublisher->unpublish($alreadyPublished)->shouldBeCalled();
        $om->remove($alreadyPublished)->shouldBeCalled();
        $om->persist($published)->shouldBeCalled();
        $om->flush()->shouldBeCalled();

        $this->publish($product);
    }

    function it_unpublishes_a_product(
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

    function it_unpublishes_products(
        $eventDispatcher,
        $unpublisher,
        $manager,
        ObjectManager $om,
        PublishedProductInterface $published1,
        PublishedProductInterface $published2,
        ProductInterface $product
    )
    {
        $manager->getObjectManager()->willReturn($om);

        $published1->getOriginalProduct()->willReturn($product);
        $published2->getOriginalProduct()->willReturn($product);

        $unpublisher->unpublish($published1)->shouldBeCalled();
        $unpublisher->unpublish($published2)->shouldBeCalled();

        $eventDispatcher->dispatch(PublishedProductEvents::PRE_UNPUBLISH, Argument::cetera())->shouldBeCalled();

        $om->remove($published1)->shouldBeCalled();
        $om->remove($published2)->shouldBeCalled();
        $om->flush()->shouldBeCalledTimes(1);

        $this->unpublishAll([$published1, $published2]);
    }

    function it_returns_the_published_repository($repository)
    {
        $this->getProductRepository()->shouldReturn($repository);
    }

    function it_returns_the_product_manager_s_attribute_repository($manager, AttributeRepositoryInterface $repository)
    {
        $manager->getAttributeRepository()->willReturn($repository);
        $this->getAttributeRepository()->shouldReturn($repository);
    }
}
