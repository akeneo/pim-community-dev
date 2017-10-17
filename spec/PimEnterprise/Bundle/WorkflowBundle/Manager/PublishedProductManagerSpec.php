<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Manager;

use Akeneo\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
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
        PublishedProductRepositoryInterface $repositoryWithPermission,
        AttributeRepositoryInterface $attributeRepository,
        EventDispatcherInterface $eventDispatcher,
        PublisherInterface $publisher,
        UnpublisherInterface $unpublisher,
        ObjectManager $objectManager,
        SaverInterface $publishedProductSaver,
        RemoverInterface $remover,
        BulkRemoverInterface $bulkRemover,
        PublishedProductRepositoryInterface $repositoryWithoutPermission
    ) {
        $this->beConstructedWith(
            $productRepository,
            $repositoryWithPermission,
            $attributeRepository,
            $eventDispatcher,
            $publisher,
            $unpublisher,
            $objectManager,
            $publishedProductSaver,
            $remover,
            $bulkRemover,
            $repositoryWithoutPermission
        );
    }

    function it_publishes_a_product(
        $eventDispatcher,
        $publisher,
        $repositoryWithPermission,
        $publishedProductSaver,
        $productRepository,
        ProductInterface $product,
        PublishedProductInterface $published
    ) {
        $product->getId()->willReturn(1);
        $productRepository->find(1)->willReturn($product);
        $repositoryWithPermission->findOneByOriginalProduct(Argument::any())->willReturn(null);
        $publisher->publish($product, [])->willReturn($published);

        $eventDispatcher->dispatch(PublishedProductEvents::PRE_PUBLISH, Argument::any(), null)->shouldBeCalled();
        $eventDispatcher->dispatch(PublishedProductEvents::POST_PUBLISH, Argument::cetera())->shouldBeCalled();

        $publishedProductSaver->save($published)->shouldBeCalled();

        $this->publish($product);
    }

    function it_publishes_products_with_associations(
        $publisher,
        $repositoryWithPermission,
        $remover,
        $productRepository,
        $repositoryWithoutPermission,
        BulkSaverInterface $publishedProductSaver,
        ProductInterface $productFoo,
        ProductInterface $productBar,
        PublishedProductInterface $publishedFoo,
        PublishedProductInterface $publishedBar,
        AssociationInterface $association
    ) {
        $productFoo->getId()->willReturn(1);
        $productBar->getId()->willReturn(2);
        $productRepository->find(1)->willReturn($productFoo);
        $productRepository->find(2)->willReturn($productBar);
        $publishedFoo->getOriginalProduct()->willReturn($productFoo);
        $publishedBar->getOriginalProduct()->willReturn($productBar);

        $repositoryWithPermission->findOneByOriginalProduct($productBar)->willReturn($publishedFoo);
        $repositoryWithPermission->findOneByOriginalProduct($productFoo)->willReturn($publishedBar);

        $publisher->publish($productFoo, ['with_associations' => false, 'flush' => false])->willReturn($publishedFoo);
        $publisher->publish($productBar, ['with_associations' => false, 'flush' => false])->willReturn($publishedBar);

        $repositoryWithoutPermission->findOneByOriginalProduct($productBar)->willReturn($publishedBar);
        $repositoryWithoutPermission->findOneByOriginalProduct($productFoo)->willReturn($publishedFoo);

        $publishedProductSaver->saveAll([$publishedFoo, $publishedBar])->shouldBeCalled();

        $productFoo->getAssociations()->willReturn([$association]);
        $productBar->getAssociations()->willReturn([$association]);

        $publishedFoo->addAssociation($association)->shouldBeCalled();
        $publishedBar->addAssociation($association)->shouldBeCalled();

        $publisher->publish($association, ['published' => $publishedFoo])->willReturn($association);
        $publisher->publish($association, ['published' => $publishedBar])->willReturn($association);

        $remover->remove(Argument::any())->shouldBeCalled();

        $this->publishAll([$productFoo, $productBar]);
    }

    function it_publishes_a_product_already_published(
        $eventDispatcher,
        $publisher,
        $unpublisher,
        $productRepository,
        $remover,
        $publishedProductSaver,
        $repositoryWithoutPermission,
        ProductInterface $filteredProduct,
        PublishedProductInterface $alreadyPublished,
        PublishedProductInterface $published,
        ProductInterface $fullProduct
    ) {
        $repositoryWithoutPermission->findOneByOriginalProduct(Argument::any())->willReturn($alreadyPublished);
        $productRepository->find(1)->willReturn($fullProduct);
        $publisher->publish($fullProduct, [])->willReturn($published);
        $filteredProduct->getId()->willReturn(1);

        $eventDispatcher->dispatch(PublishedProductEvents::PRE_PUBLISH, Argument::any(), null)->shouldBeCalled();
        $eventDispatcher->dispatch(PublishedProductEvents::POST_PUBLISH, Argument::cetera())->shouldBeCalled();

        $unpublisher->unpublish($alreadyPublished)->shouldBeCalled();
        $remover->remove($alreadyPublished)->shouldBeCalled();

        $publishedProductSaver->save($published)->shouldBeCalled();

        $this->publish($filteredProduct);
    }

    function it_unpublishes_a_product(
        $eventDispatcher,
        $unpublisher,
        $remover,
        $repositoryWithoutPermission,
        PublishedProductInterface $fullPublished,
        PublishedProductInterface $filteredPublished,
        ProductInterface $product
    ) {
        $filteredPublished->getId()->willReturn(1);

        $repositoryWithoutPermission->find(1)->willReturn($fullPublished);
        $fullPublished->getOriginalProduct()->willReturn($product);
        $unpublisher->unpublish($fullPublished)->shouldBeCalled();

        $eventDispatcher->dispatch(PublishedProductEvents::PRE_UNPUBLISH, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(PublishedProductEvents::POST_UNPUBLISH, Argument::any(), null)->shouldBeCalled();

        $remover->remove($fullPublished)->shouldBeCalled();

        $this->unpublish($filteredPublished);
    }

    function it_unpublishes_products(
        $eventDispatcher,
        $unpublisher,
        $bulkRemover,
        $repositoryWithoutPermission,
        PublishedProductInterface $fullPublished1,
        PublishedProductInterface $filteredPublished1,
        PublishedProductInterface $fullPublished2,
        PublishedProductInterface $filteredPublished2,
        ProductInterface $product
    ) {
        $filteredPublished1->getId()->willReturn(1);
        $filteredPublished2->getId()->willReturn(2);

        $repositoryWithoutPermission->find(1)->willReturn($fullPublished1);
        $repositoryWithoutPermission->find(2)->willReturn($fullPublished2);
        $fullPublished1->getOriginalProduct()->willReturn($product);
        $fullPublished2->getOriginalProduct()->willReturn($product);

        $unpublisher->unpublish($fullPublished1)->shouldBeCalled();
        $unpublisher->unpublish($fullPublished2)->shouldBeCalled();

        $eventDispatcher->dispatch(PublishedProductEvents::PRE_UNPUBLISH, Argument::cetera())->shouldBeCalled();

        $bulkRemover->removeAll([$fullPublished1, $fullPublished2])->shouldBeCalled();

        $this->unpublishAll([$filteredPublished1, $filteredPublished2]);
    }
}
