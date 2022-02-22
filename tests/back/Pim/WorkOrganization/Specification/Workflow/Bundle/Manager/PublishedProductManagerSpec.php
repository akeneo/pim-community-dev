<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Manager;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Manager\PublishedProductManager;
use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Persistence\ObjectManager;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Event\PublishedProductEvents;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Publisher\PublisherInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Publisher\UnpublisherInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\PublishedProductRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PublishedProductManagerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PublishedProductManager::class);
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
        BulkSaverInterface $publishedProductBulkSaver,
        RemoverInterface $remover,
        BulkRemoverInterface $bulkRemover,
        PublishedProductRepositoryInterface $repositoryWithoutPermission
    ) {
        $eventDispatcher->dispatch(Argument::any(), Argument::type('string'))->willReturn(Argument::type('object'));
        $this->beConstructedWith(
            $productRepository,
            $repositoryWithPermission,
            $attributeRepository,
            $eventDispatcher,
            $publisher,
            $unpublisher,
            $objectManager,
            $publishedProductSaver,
            $publishedProductBulkSaver,
            $remover,
            $bulkRemover,
            $repositoryWithoutPermission,
            [1, 1, 1]
        );
    }

    function it_publishes_a_product(
        EventDispatcherInterface $eventDispatcher,
        PublisherInterface $publisher,
        PublishedProductRepositoryInterface $repositoryWithPermission,
        SaverInterface $publishedProductSaver,
        ProductRepositoryInterface $productRepository,
        ProductInterface $product,
        PublishedProductInterface $published
    ) {
        $product->getId()->willReturn(1);
        $productRepository->find(1)->willReturn($product);
        $repositoryWithPermission->findOneByOriginalProduct(Argument::any())->willReturn(null);
        $publisher->publish($product, [])->willReturn($published);

        $eventDispatcher->dispatch(Argument::any(), PublishedProductEvents::PRE_PUBLISH, null)->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::any(), PublishedProductEvents::POST_PUBLISH)->shouldBeCalled();

        $publishedProductSaver->save($published, ['add_default_values' => false])->shouldBeCalled();

        $this->publish($product);
    }

    function it_publishes_products_with_associations(
        PublisherInterface $publisher,
        PublishedProductRepositoryInterface $repositoryWithPermission,
        RemoverInterface $remover,
        ProductRepositoryInterface $productRepository,
        PublishedProductRepositoryInterface $repositoryWithoutPermission,
        BulkSaverInterface $publishedProductBulkSaver,
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

        $publishedProductBulkSaver->saveAll([$publishedFoo, $publishedBar], ['add_default_values' => false])->shouldBeCalled();

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
        EventDispatcherInterface $eventDispatcher,
        PublisherInterface $publisher,
        UnpublisherInterface $unpublisher,
        ProductRepositoryInterface $productRepository,
        RemoverInterface $remover,
        SaverInterface $publishedProductSaver,
        PublishedProductRepositoryInterface $repositoryWithoutPermission,
        ProductInterface $filteredProduct,
        PublishedProductInterface $alreadyPublished,
        PublishedProductInterface $published,
        ProductInterface $fullProduct
    ) {
        $repositoryWithoutPermission->findOneByOriginalProduct(Argument::any())->willReturn($alreadyPublished);
        $productRepository->find(1)->willReturn($fullProduct);
        $publisher->publish($fullProduct, [])->willReturn($published);
        $filteredProduct->getId()->willReturn(1);

        $eventDispatcher->dispatch(Argument::any(), PublishedProductEvents::PRE_PUBLISH, null)->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::any(), PublishedProductEvents::POST_PUBLISH)->shouldBeCalled();

        $unpublisher->unpublish($alreadyPublished)->shouldBeCalled();
        $remover->remove($alreadyPublished)->shouldBeCalled();

        $publishedProductSaver->save($published, ['add_default_values' => false])->shouldBeCalled();

        $this->publish($filteredProduct);
    }

    function it_unpublishes_a_product(
        EventDispatcherInterface $eventDispatcher,
        UnpublisherInterface $unpublisher,
        RemoverInterface $remover,
        PublishedProductRepositoryInterface $repositoryWithoutPermission,
        PublishedProductInterface $fullPublished,
        PublishedProductInterface $filteredPublished,
        ProductInterface $product
    ) {
        $filteredPublished->getId()->willReturn(1);

        $repositoryWithoutPermission->find(1)->willReturn($fullPublished);
        $fullPublished->getOriginalProduct()->willReturn($product);
        $unpublisher->unpublish($fullPublished)->shouldBeCalled();

        $eventDispatcher->dispatch(Argument::any(), PublishedProductEvents::PRE_UNPUBLISH)->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::any(), PublishedProductEvents::POST_UNPUBLISH, null)->shouldBeCalled();

        $remover->remove($fullPublished)->shouldBeCalled();

        $this->unpublish($filteredPublished);
    }

    function it_unpublishes_products(
        EventDispatcherInterface $eventDispatcher,
        UnpublisherInterface $unpublisher,
        BulkRemoverInterface $bulkRemover,
        PublishedProductRepositoryInterface $repositoryWithoutPermission,
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

        $eventDispatcher->dispatch(Argument::any(), PublishedProductEvents::PRE_UNPUBLISH)->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::any(), PublishedProductEvents::POST_UNPUBLISH)->shouldBeCalled();

        $bulkRemover->removeAll([$fullPublished1, $fullPublished2])->shouldBeCalled();

        $this->unpublishAll([$filteredPublished1, $filteredPublished2]);
    }

    function it_retries_when_removing_an_already_published_product_failed(
        PublishedProductRepositoryInterface $repositoryWithoutPermission,
        ProductRepositoryInterface $productRepository,
        PublisherInterface $publisher,
        EventDispatcherInterface $eventDispatcher,
        UnpublisherInterface $unpublisher,
        RemoverInterface $remover,
        SaverInterface $publishedProductSaver,
        PublishedProductInterface $alreadyPublished,
        ProductInterface $filteredProduct,
        ProductInterface $fullProduct
    ) {
        $productRepository->find(1)->willReturn($fullProduct);
        $filteredProduct->getId()->willReturn(1);
        $alreadyPublished->getId()->willReturn(1);
        $repositoryWithoutPermission->findOneByOriginalProduct(Argument::any())->willReturn($alreadyPublished);
        $repositoryWithoutPermission->find(1)->willReturn($alreadyPublished);
        $alreadyPublished->getOriginalProduct()->willReturn($fullProduct);

        $eventDispatcher->dispatch(Argument::any(), PublishedProductEvents::PRE_PUBLISH)->shouldBeCalled();
        $unpublisher->unpublish($alreadyPublished)->shouldBeCalled();
        $remover->remove($alreadyPublished)->shouldBeCalledTimes(4)->willThrow(new Missing404Exception("test message"));

        $publisher->publish(Argument::any())->shouldNotBeCalled();
        $eventDispatcher->dispatch(Argument::any(), PublishedProductEvents::POST_PUBLISH)->shouldNotBeCalled();
        $publishedProductSaver->save(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(new Missing404Exception("test message"))->during('publish', [$filteredProduct]);
    }
}
