<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Product\OnDelete;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Product\OnDelete\ComputeProductsAndAncestorsSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;

class ComputeProductsAndAncestorsSubscriberSpec extends ObjectBehavior
{
    function let(ProductAndAncestorsIndexer $indexer, Client $esClient)
    {
        $this->beConstructedWith($indexer, $esClient);
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
        $indexer->removeFromProductUuidsAndReindexAncestors(Argument::cetera())->shouldNotBeCalled();

        $this->deleteProduct(new RemoveEvent(
            Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'),
            new \stdClass(),
            ['unitary' => true]
        ));
        $this->deleteProduct(new RemoveEvent(
            [Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'), Uuid::fromString('d9f573cc-8905-4949-8151-baf9d5328f26')],
            [new \stdClass(), new ProductModel()],
            ['unitary' => false]
        ));
    }

    function it_does_not_delete_single_products_on_non_unitary_events(ProductAndAncestorsIndexer $indexer)
    {
        $this->deleteProduct(new RemoveEvent(new Product(), Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'), ['unitary' => false]));

        $indexer->removeFromProductUuidsAndReindexAncestors(Argument::cetera())->shouldNotBeCalled();
    }

    function it_deletes_a_single_product_from_the_index(ProductAndAncestorsIndexer $indexer, Client $esClient)
    {
        $product = new Product();
        $product->setCreated(new \DateTime('1970-01-01'));
        $this->deleteProduct(new RemoveEvent($product, Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'), ['unitary' => true]));
        $esClient->refreshIndex()->shouldNotBeCalled();

        $indexer->removeFromProductUuidsAndReindexAncestors([Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5')], [])->shouldBeCalled();
    }

    function it_refreshes_index_before_deleting_a_single_product(
        ProductAndAncestorsIndexer $indexer,
        Client $esClient
    ) {
        $product = new Product();
        $product->setCreated((new \DateTime('now'))->modify("- 1 second"));
        $this->deleteProduct(new RemoveEvent($product, Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'), ['unitary' => true]));
        $esClient->refreshIndex()->shouldBeCalledOnce();

        $indexer->removeFromProductUuidsAndReindexAncestors([Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5')], [])->shouldBeCalled();
    }

    function it_deletes_a_single_variant_product_from_the_index(
        ProductAndAncestorsIndexer $indexer
    ) {
        $rootProductModel = new ProductModel();
        $rootProductModel->setCode('root');
        $subProductModel = new ProductModel();
        $subProductModel->setCode('sub');
        $subProductModel->setParent($rootProductModel);
        $variantProduct = new Product();
        $variantProduct->setParent($subProductModel);
        $variantProduct->setCreated(new \DateTime('1970-01-01'));

        $indexer->removeFromProductUuidsAndReindexAncestors(
            [$variantProduct->getUuid()],
            ['sub', 'root']
        )->shouldBeCalled();

        $event = new RemoveEvent($variantProduct, $variantProduct->getUuid(), ['unitary' => true]);
        $this->deleteProduct($event);
    }

    function it_deletes_multiple_products_from_the_index(
        ProductAndAncestorsIndexer $indexer,
        Client $esClient
    ) {
        $rootProductModel = new ProductModel();
        $rootProductModel->setCode('root');
        $subProductModel1 = new ProductModel();
        $subProductModel1->setCode('sub1');
        $subProductModel1->setParent($rootProductModel);
        $variantProduct = new Product();
        $variantProduct->setParent($subProductModel1);
        $variantProduct->setCreated(new \DateTime('1970-01-01'));
        $subProductModel2 = new ProductModel();
        $subProductModel2->setCode('sub2');
        $subProductModel2->setParent($rootProductModel);
        $otherVariantProduct = new Product();
        $otherVariantProduct->setParent($subProductModel2);
        $otherVariantProduct->setCreated((new \DateTime('now'))->modify("- 1 second"));

        $indexer->removeFromProductUuidsAndReindexAncestors(
            [$variantProduct->getUuid(), $otherVariantProduct->getUuid()],
            ['sub1', 'root', 'sub2']
        )->shouldBeCalled();

        $esClient->refreshIndex()->shouldBeCalledOnce();

        $event = new RemoveEvent(
            [$variantProduct, $otherVariantProduct],
            [$variantProduct->getUuid(), $otherVariantProduct->getUuid()]
        );

        $this->deleteProducts($event);
    }
}
