<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AssociationInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
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
        ProductRepositoryInterface $productRepository,
        PublishedProductRepositoryInterface $repository,
        AttributeRepositoryInterface $attributeRepository,
        EventDispatcherInterface $eventDispatcher,
        PublisherInterface $publisher,
        UnpublisherInterface $unpublisher,
        ObjectManager $objectManager
    ) {
        $this->beConstructedWith(
            $productRepository,
            $repository,
            $attributeRepository,
            $eventDispatcher,
            $publisher,
            $unpublisher,
            $objectManager
        );
    }

    function it_is_a_configurable_step_element()
    {
        $this->beAnInstanceOf('Pim\Bundle\CatalogBundle\Manager\ProductManagerInterface');
    }

    function it_publishes_a_product(
        $eventDispatcher,
        $publisher,
        $repository,
        $objectManager,
        ProductInterface $product,
        PublishedProductInterface $published
    ) {
        $repository->findOneByOriginalProduct(Argument::any())->willReturn(null);
        $publisher->publish($product, [])->willReturn($published);

        $eventDispatcher->dispatch(PublishedProductEvents::PRE_PUBLISH, Argument::any(), null)->shouldBeCalled();
        $eventDispatcher->dispatch(PublishedProductEvents::POST_PUBLISH, Argument::cetera())->shouldBeCalled();

        $objectManager->persist($published)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->publish($product);
    }

    function it_publishes_products(
        $publisher,
        $repository,
        ProductInterface $productFoo,
        ProductInterface $productBar,
        $objectManager,
        PublishedProductInterface $publishedFoo,
        PublishedProductInterface $publishedBar,
        AssociationInterface $association
    ) {
        $repository->findOneByOriginalProduct($productBar)->willReturn($publishedFoo);
        $repository->findOneByOriginalProduct($productFoo)->willReturn($publishedBar);

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

        $objectManager->remove(Argument::any())->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $objectManager->persist($publishedFoo)->shouldBeCalledTimes(1);
        $objectManager->persist($publishedBar)->shouldBeCalledTimes(1);

        $objectManager->flush()->shouldBeCalledTimes(4);

        $this->publishAll([$productFoo, $productBar]);
    }

    function it_publishes_a_product_already_published(
        $eventDispatcher,
        $publisher,
        $unpublisher,
        $repository,
        $objectManager,
        ProductInterface $product,
        PublishedProductInterface $alreadyPublished,
        PublishedProductInterface $published
    ) {
        $repository->findOneByOriginalProduct(Argument::any())->willReturn($alreadyPublished);
        $publisher->publish($product, [])->willReturn($published);

        $eventDispatcher->dispatch(PublishedProductEvents::PRE_PUBLISH, Argument::any(), null)->shouldBeCalled();
        $eventDispatcher->dispatch(PublishedProductEvents::POST_PUBLISH, Argument::cetera())->shouldBeCalled();

        $unpublisher->unpublish($alreadyPublished)->shouldBeCalled();
        $objectManager->remove($alreadyPublished)->shouldBeCalled();
        $objectManager->persist($published)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->publish($product);
    }

    function it_unpublishes_a_product(
        $eventDispatcher,
        $unpublisher,
        $objectManager,
        PublishedProductInterface $published,
        ProductInterface $product
    ) {
        $published->getOriginalProduct()->willReturn($product);
        $unpublisher->unpublish($published)->shouldBeCalled();

        $eventDispatcher->dispatch(PublishedProductEvents::PRE_UNPUBLISH, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(PublishedProductEvents::POST_UNPUBLISH, Argument::any(), null)->shouldBeCalled();

        $objectManager->remove($published)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->unpublish($published);
    }

    function it_unpublishes_products(
        $eventDispatcher,
        $unpublisher,
        $objectManager,
        PublishedProductInterface $published1,
        PublishedProductInterface $published2,
        ProductInterface $product
    )
    {
        $published1->getOriginalProduct()->willReturn($product);
        $published2->getOriginalProduct()->willReturn($product);

        $unpublisher->unpublish($published1)->shouldBeCalled();
        $unpublisher->unpublish($published2)->shouldBeCalled();

        $eventDispatcher->dispatch(PublishedProductEvents::PRE_UNPUBLISH, Argument::cetera())->shouldBeCalled();

        $objectManager->remove($published1)->shouldBeCalled();
        $objectManager->remove($published2)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalledTimes(1);

        $this->unpublishAll([$published1, $published2]);
    }

    function it_returns_the_published_repository($repository)
    {
        $this->getProductRepository()->shouldReturn($repository);
    }

    function it_returns_the_product_manager_s_attribute_repository($attributeRepository)
    {
        $this->getAttributeRepository()->shouldReturn($attributeRepository);
    }
}
