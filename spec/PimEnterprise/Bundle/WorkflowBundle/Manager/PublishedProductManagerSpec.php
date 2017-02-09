<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Manager;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AssociationInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use PimEnterprise\Component\Workflow\Event\PublishedProductEvents;
use PimEnterprise\Component\Workflow\Model\PublishedProductInterface;
use PimEnterprise\Component\Workflow\Publisher\PublisherInterface;
use PimEnterprise\Component\Workflow\Publisher\UnpublisherInterface;
use PimEnterprise\Component\Workflow\Repository\PublishedProductRepositoryInterface;
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
        ObjectManager $objectManager,
        SaverInterface $publishedProductSaver
    ) {
        $this->beConstructedWith(
            $productRepository,
            $repository,
            $attributeRepository,
            $eventDispatcher,
            $publisher,
            $unpublisher,
            $objectManager,
            $publishedProductSaver
        );
    }

    function it_publishes_a_product(
        $eventDispatcher,
        $publisher,
        $repository,
        $publishedProductSaver,
        ProductInterface $product,
        PublishedProductInterface $published
    ) {
        $repository->findOneByOriginalProduct(Argument::any())->willReturn(null);
        $publisher->publish($product, [])->willReturn($published);

        $eventDispatcher->dispatch(PublishedProductEvents::PRE_PUBLISH, Argument::any(), null)->shouldBeCalled();
        $eventDispatcher->dispatch(PublishedProductEvents::POST_PUBLISH, Argument::cetera())->shouldBeCalled();

        $publishedProductSaver->save($published)->shouldBeCalled();

        $this->publish($product);
    }

    function it_publishes_products_with_associations(
        $publisher,
        $repository,
        BulkSaverInterface $publishedProductSaver,
        ProductInterface $productFoo,
        ProductInterface $productBar,
        $objectManager,
        PublishedProductInterface $publishedFoo,
        PublishedProductInterface $publishedBar,
        AssociationInterface $association
    ) {
        $publishedFoo->getOriginalProduct()->willReturn($productFoo);
        $publishedBar->getOriginalProduct()->willReturn($productBar);

        $repository->findOneByOriginalProduct($productBar)->willReturn($publishedFoo);
        $repository->findOneByOriginalProduct($productFoo)->willReturn($publishedBar);

        $publisher->publish($productFoo, ['with_associations' => false, 'flush' => false])->willReturn($publishedFoo);
        $publisher->publish($productBar, ['with_associations' => false, 'flush' => false])->willReturn($publishedBar);

        $publishedProductSaver->saveAll([$publishedFoo, $publishedBar])->shouldBeCalled();
        $publishedProductSaver->saveAll([$publishedBar, $publishedFoo])->shouldBeCalled();

        $productFoo->getAssociations()->willReturn([$association]);
        $productBar->getAssociations()->willReturn([$association]);

        $publishedFoo->addAssociation($association)->shouldBeCalled();
        $publishedBar->addAssociation($association)->shouldBeCalled();

        $publisher->publish($association, ['published' => $publishedFoo])->willReturn($association);
        $publisher->publish($association, ['published' => $publishedBar])->willReturn($association);


        $objectManager->remove(Argument::any())->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->publishAll([$productFoo, $productBar]);
    }

    function it_publishes_a_product_already_published(
        $eventDispatcher,
        $publisher,
        $unpublisher,
        $repository,
        $objectManager,
        $publishedProductSaver,
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
        $objectManager->flush()->shouldBeCalled();

        $publishedProductSaver->save($published)->shouldBeCalled();

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
}
