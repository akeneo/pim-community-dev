<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Product\OnDelete;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Product\OnDelete\ComputeProductsAndAncestorsSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\DBAL\Connection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;

class ComputeProductsAndAncestorsSubscriberSpec extends ObjectBehavior
{
    function let(ProductAndAncestorsIndexer $indexer, Connection $connection, Client $esClient)
    {
        $this->beConstructedWith($indexer, $connection, $esClient);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ComputeProductsAndAncestorsSubscriber::class);
    }

    function it_subscribes_to_remove_events()
    {
        $this::getSubscribedEvents()->shouldHaveKey(StorageEvents::PRE_REMOVE);
        $this::getSubscribedEvents()->shouldHaveKey(StorageEvents::PRE_REMOVE_ALL);
        $this::getSubscribedEvents()->shouldHaveKey(StorageEvents::POST_REMOVE);
        $this::getSubscribedEvents()->shouldHaveKey(StorageEvents::POST_REMOVE_ALL);
    }

    function it_only_handles_products(ProductAndAncestorsIndexer $indexer)
    {
        $indexer->removeFromProductIdsAndReindexAncestors(Argument::cetera())->shouldNotBeCalled();

        $this->deleteProduct(new RemoveEvent(42, new \stdClass(), ['unitary' => true]));
        $this->deleteProduct(new RemoveEvent([42, 23],  [new \stdClass(), new ProductModel()], ['unitary' => false]));
    }

    function it_does_not_delete_single_products_on_non_unitary_events(ProductAndAncestorsIndexer $indexer)
    {
        $this->deleteProduct(new RemoveEvent(new Product(), 42, ['unitary' => false]));

        $indexer->removeFromProductIdsAndReindexAncestors(Argument::cetera())->shouldNotBeCalled();
    }

    function it_deletes_a_single_product_from_the_index(ProductAndAncestorsIndexer $indexer, Client $esClient)
    {
        $product = new Product();
        $product->setCreated(new \DateTime('1970-01-01'));
        $this->deleteProduct(new RemoveEvent($product, 42, ['unitary' => true]));
        $esClient->refreshIndex()->shouldNotBeCalled();

        $indexer->removeFromProductIdsAndReindexAncestors([42], [], [])->shouldBeCalled();
    }

    function it_refreshes_index_before_deleting_a_single_product(
        ProductAndAncestorsIndexer $indexer,
        Client $esClient
    ) {
        $product = new Product();
        $product->setCreated((new \DateTime('now'))->modify("- 1 second"));
        $this->deleteProduct(new RemoveEvent($product, 42, ['unitary' => true]));
        $esClient->refreshIndex()->shouldBeCalledOnce();

        $indexer->removeFromProductIdsAndReindexAncestors([42], [], [])->shouldBeCalled();
    }

    function it_deletes_a_single_variant_product_from_the_index(
        ProductAndAncestorsIndexer $indexer,
        Connection $connection
    ) {
        $rootProductModel = new ProductModel();
        $rootProductModel->setCode('root');
        $subProductModel = new ProductModel();
        $subProductModel->setCode('sub');
        $subProductModel->setParent($rootProductModel);
        $variantProduct = new Product();
        $variantProduct->setParent($subProductModel);
        $variantProduct->setCreated(new \DateTime('1970-01-01'));

        $indexer->removeFromProductIdsAndReindexAncestors(
            [100],
            [Uuid::fromString('386f0ec8-4e4c-4028-acd7-e1195a13a3b5')],
            ['sub', 'root']
        )->shouldBeCalled();

        $connection->fetchAllAssociative(Argument::any())->willReturn(['The uuid column exists']);
        $connection->fetchFirstColumn(Argument::any(), ['product_ids' => [100]], Argument::any())->willReturn(['386f0ec8-4e4c-4028-acd7-e1195a13a3b5']);

        $event = new RemoveEvent($variantProduct, 100, ['unitary' => true]);
        $this->setProductUuidCache($event);
        $this->deleteProduct($event);
    }

    function it_deletes_multiple_products_from_the_index(
        ProductAndAncestorsIndexer $indexer,
        Connection $connection,
        Client $esClient
    ) {
        $rootProductModel = new ProductModel();
        $rootProductModel->setCode('root');
        $subProductModel1 = new ProductModel();
        $subProductModel1->setCode('sub1');
        $subProductModel1->setParent($rootProductModel);
        $variantProduct = new Product();
        $variantProduct->setParent($subProductModel1);
        $variantProduct->setId(44);
        $variantProduct->setCreated(new \DateTime('1970-01-01'));
        $subProductModel2 = new ProductModel();
        $subProductModel2->setCode('sub2');
        $subProductModel2->setParent($rootProductModel);
        $otherVariantProduct = new Product();
        $otherVariantProduct->setParent($subProductModel2);
        $otherVariantProduct->setId(56);
        $otherVariantProduct->setCreated((new \DateTime('now'))->modify("- 1 second"));

        $indexer->removeFromProductIdsAndReindexAncestors(
            [44, 56],
            [
                Uuid::fromString('386f0ec8-4e4c-4028-acd7-e1195a13a3b5'),
                Uuid::fromString('57e9847a-6c56-4403-9f1f-abde22ecb0a4'),
            ],
            ['sub1', 'root', 'sub2']
        )->shouldBeCalled();

        $connection
            ->fetchFirstColumn(Argument::any(), ['product_ids' => [44, 56]], Argument::any())
            ->shouldBeCalled()
            ->willReturn([
                '386f0ec8-4e4c-4028-acd7-e1195a13a3b5',
                '57e9847a-6c56-4403-9f1f-abde22ecb0a4',
            ]);
        $esClient->refreshIndex()->shouldBeCalledOnce();

        $event = new RemoveEvent(
            [$variantProduct, $otherVariantProduct],
            [44, 56]
        );

        $this->setProductUuidsCache($event);
        $this->deleteProducts($event);
    }
}
