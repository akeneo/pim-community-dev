<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\PublishedProduct\OnSave;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculator;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodes;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Indexer\PublishedProductIndexer;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\PublishedProduct\OnSave\ComputePublishedProductsSubscriber;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompletenessCollection;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProduct;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\SavePublishedProductCompletenesses;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class ComputePublishedProductsSubscriberSpec extends ObjectBehavior
{
    function let(
        SavePublishedProductCompletenesses $savePublishedProductCompletenesses,
        CompletenessCalculator $completenessCalculator,
        PublishedProductIndexer $publishedProductIndexer
    ) {
        $this->beConstructedWith(
            $savePublishedProductCompletenesses,
            $completenessCalculator,
            $publishedProductIndexer
        );
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_is_a_compute_published_product_event_subscriber()
    {
        $this->shouldHaveType(ComputePublishedProductsSubscriber::class);
    }

    function it_subscribes_to_post_save_events()
    {
        $this::getSubscribedEvents()->shouldHaveKey(StorageEvents::POST_SAVE);
        $this::getSubscribedEvents()->shouldHaveKey(StorageEvents::POST_SAVE_ALL);
    }

    function it_only_handles_published_products(
        SavePublishedProductCompletenesses $savePublishedProductCompletenesses,
        PublishedProductIndexer $publishedProductIndexer
    ) {
        $savePublishedProductCompletenesses->save(Argument::cetera())->shouldNotBeCalled();
        $publishedProductIndexer->indexAll(Argument::any())->shouldNotBeCalled();

        $this->computePublishedProduct(new GenericEvent(new \stdClass(), ['unitary' => true]));
        $this->computeMultiplePublishedProducts(
            new GenericEvent([new \stdClass(), new Product()], ['unitary' => false])
        );
    }

    function it_does_nothing_on_post_save_for_non_unitary_event(
        SavePublishedProductCompletenesses $savePublishedProductCompletenesses,
        PublishedProductIndexer $publishedProductIndexer
    ) {
        $savePublishedProductCompletenesses->save(Argument::any())->shouldNotBeCalled();
        $publishedProductIndexer->index(Argument::cetera())->shouldNotBeCalled();

        $this->computePublishedProduct(new GenericEvent(new PublishedProduct(), ['unitary' => false]));
    }

    function it_computes_completenesses_and_indexes_a_published_product(
        SavePublishedProductCompletenesses $savePublishedProductCompletenesses,
        CompletenessCalculator $completenessCalculator,
        PublishedProductIndexer $publishedProductIndexer
    ) {
        $product = new Product();
        $product->setIdentifier('original_product');
        $publishedProduct = new PublishedProduct();
        $publishedProduct->setId(42);
        $publishedProduct->setOriginalProduct($product);

        $completenessCalculator->fromProductUuids([$product->getUuid()])
            ->willReturn([
                'original_product' => new ProductCompletenessWithMissingAttributeCodesCollection(
                    56,
                    [
                        new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 5, []),
                        new ProductCompletenessWithMissingAttributeCodes('print', 'en_US', 6, ['description']),
                    ]
                )
            ]);

        $savePublishedProductCompletenesses
            ->save(Argument::type(PublishedProductCompletenessCollection::class))
            ->shouldBeCalled();
        $publishedProductIndexer->indexAll([$publishedProduct], ['index_refresh' => Refresh::disable()])->shouldBeCalled();

        $this->computePublishedProduct(new GenericEvent($publishedProduct, ['unitary' => true]));
    }

    function it_computes_completeness_and_indexes_several_published_products(
        SavePublishedProductCompletenesses $savePublishedProductCompletenesses,
        CompletenessCalculator $completenessCalculator,
        PublishedProductIndexer $publishedProductIndexer
    ) {
        $product = new Product();
        $product->setIdentifier('original_product');
        $publishedProduct = new PublishedProduct();
        $publishedProduct->setId(42);
        $publishedProduct->setOriginalProduct($product);

        $otherProduct = new Product();
        $otherProduct->setIdentifier('other_original_product');
        $otherPublishedProduct = new PublishedProduct();
        $otherPublishedProduct->setId(45);
        $otherPublishedProduct->setOriginalProduct($otherProduct);

        $completenessCalculator->fromProductUuids([$product->getUuid(), $otherProduct->getUuid()])
            ->willReturn(
                [
                    'original_product' => new ProductCompletenessWithMissingAttributeCodesCollection(
                        56,
                        [
                            new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 5, []),
                            new ProductCompletenessWithMissingAttributeCodes('print', 'en_US', 6, ['description']),
                        ]
                    ),
                    'other_original_product' => new ProductCompletenessWithMissingAttributeCodesCollection(
                        144,
                        [
                            new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 5, ['name']),
                            new ProductCompletenessWithMissingAttributeCodes('print', 'en_US', 6, ['name', 'description']),
                        ]
                    ),
                ]
            );

        $savePublishedProductCompletenesses->save(Argument::type(PublishedProductCompletenessCollection::class))
            ->shouldBeCalledTimes(2);
        $publishedProductIndexer->indexAll([$publishedProduct, $otherPublishedProduct], ['index_refresh' => Refresh::disable()])
            ->shouldBeCalled();

        $this->computeMultiplePublishedProducts(
            new GenericEvent(
                [$publishedProduct, $otherPublishedProduct],
                ['unitary' => false]
            )
        );
    }
}
