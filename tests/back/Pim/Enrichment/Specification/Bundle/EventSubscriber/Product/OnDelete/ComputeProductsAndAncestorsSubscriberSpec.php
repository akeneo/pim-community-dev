<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Product\OnDelete;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Product\OnDelete\ComputeProductsAndAncestorsSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ComputeProductsAndAncestorsSubscriberSpec extends ObjectBehavior
{
    function let(ProductAndAncestorsIndexer $indexer)
    {
        $this->beConstructedWith($indexer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ComputeProductsAndAncestorsSubscriber::class);
    }

    function it_subscribes_to_remove_events()
    {
        $this::getSubscribedEvents()->shouldHaveKey(StorageEvents::POST_REMOVE);
        $this::getSubscribedEvents()->shouldHaveKey(StorageEvents::POST_REMOVE_ALL);
    }

    function it_only_handles_products(ProductAndAncestorsIndexer $indexer)
    {
        $this->deleteProduct(new RemoveEvent(42, new \stdClass(), ['unitary' => true]));
        $this->deleteProduct(new RemoveEvent([42, 23],  [new \stdClass(), new ProductModel()], ['unitary' => false]));

        $indexer->removeFromProductIdsAndReindexAncestors(Argument::cetera())->shouldNotBeCalled();
    }

    function it_does_not_delete_single_products_on_non_unitary_events(ProductAndAncestorsIndexer $indexer)
    {
        $indexer->removeFromProductIdsAndReindexAncestors(Argument::cetera())->shouldNotBeCalled();

        $this->deleteProduct(new RemoveEvent(new Product(), 42, ['unitary' => false]));
    }

    function it_deletes_a_single_product_from_the_index(ProductAndAncestorsIndexer $indexer)
    {
        $indexer->removeFromProductIdsAndReindexAncestors([42], [])->shouldBeCalled();

        $this->deleteProduct(new RemoveEvent(new Product(), 42, ['unitary' => true]));
    }

    function it_deletes_a_single_variant_product_from_the_index(ProductAndAncestorsIndexer $indexer)
    {
        $rootProductModel = new ProductModel();
        $rootProductModel->setCode('root');
        $subProductModel = new ProductModel();
        $subProductModel->setCode('sub');
        $subProductModel->setParent($rootProductModel);
        $variantProduct = new Product();
        $variantProduct->setParent($subProductModel);

        $indexer->removeFromProductIdsAndReindexAncestors([100], ['sub', 'root'])->shouldBeCalled();

        $this->deleteProduct(new RemoveEvent($variantProduct, 100, ['unitary' => true]));
    }

    function it_deletes_multiple_products_from_the_index(ProductAndAncestorsIndexer $indexer)
    {
        $rootProductModel = new ProductModel();
        $rootProductModel->setCode('root');
        $subProductModel1 = new ProductModel();
        $subProductModel1->setCode('sub1');
        $subProductModel1->setParent($rootProductModel);
        $variantProduct = new Product();
        $variantProduct->setParent($subProductModel1);
        $subProductModel2 = new ProductModel();
        $subProductModel2->setCode('sub2');
        $subProductModel2->setParent($rootProductModel);
        $otherVariantProduct = new Product();
        $otherVariantProduct->setParent($subProductModel2);

        $indexer->removeFromProductIdsAndReindexAncestors([44, 56, 99], ['sub1', 'root', 'sub2'])->shouldBeCalled();

        $this->deleteProducts(new RemoveEvent(
            [$variantProduct, $otherVariantProduct, new Product()],
            [44, 56, 99]
        ));
    }
}
