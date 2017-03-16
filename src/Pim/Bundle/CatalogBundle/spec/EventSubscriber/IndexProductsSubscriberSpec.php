<?php

namespace spec\Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Bundle\CatalogBundle\EventSubscriber\IndexProductsSubscriber;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class IndexProductsSubscriberSpec extends ObjectBehavior
{
    function let(NormalizerInterface $normalizer, Client $indexer)
    {
        $this->beConstructedWith($normalizer, $indexer, 'an_index_type_for_test_purpose');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(IndexProductsSubscriber::class);
    }

    function it_subscribe_to_the_save_events()
    {
        $events = $this->getSubscribedEvents();
        $events->shouldHaveCount(2);
        $events->shouldHaveKey(StorageEvents::POST_SAVE);
        $events->shouldHaveKey(StorageEvents::POST_SAVE_ALL);
    }

    function it_does_not_index_a_non_product_entity($normalizer, $indexer, GenericEvent $event, \stdClass $subject)
    {
        $event->getSubject()->willReturn($subject);

        $normalizer->normalize(Argument::cetera())->shouldNotBeCalled();
        $indexer->index(Argument::cetera())->shouldNotBeCalled();

        $this->indexProduct($event);
    }

    function it_does_not_index_a_non_unitary_save_of_a_product(
        $normalizer,
        $indexer,
        GenericEvent $event,
        ProductInterface $product
    ) {
        $event->getSubject()->willReturn($product);
        $event->hasArgument('unitary')->willReturn(true);
        $event->getArgument('unitary')->willReturn(false);

        $normalizer->normalize(Argument::cetera())->shouldNotBeCalled();
        $indexer->index(Argument::cetera())->shouldNotBeCalled();

        $this->indexProduct($event);
    }

    function it_does_not_index_a_non_unitary_save_of_a_product_bis(
        $normalizer,
        $indexer,
        GenericEvent $event,
        ProductInterface $product
    ) {
        $event->getSubject()->willReturn($product);
        $event->hasArgument('unitary')->willReturn(false);

        $normalizer->normalize(Argument::cetera())->shouldNotBeCalled();
        $indexer->index(Argument::cetera())->shouldNotBeCalled();

        $this->indexProduct($event);
    }

    function it_does_not_bulk_index_non_product_entities(
        $normalizer,
        $indexer,
        GenericEvent $event,
        \stdClass $subject1
    ) {
        $event->getSubject()->willReturn([$subject1]);

        $normalizer->normalize(Argument::cetera())->shouldNotBeCalled();
        $indexer->index(Argument::cetera())->shouldNotBeCalled();

        $this->bulkIndexProducts($event);
    }

    function it_does_not_bulk_index_non_collections($normalizer, $indexer, GenericEvent $event, \stdClass $subject1)
    {
        $event->getSubject()->willReturn($subject1);

        $normalizer->normalize(Argument::cetera())->shouldNotBeCalled();
        $indexer->index(Argument::cetera())->shouldNotBeCalled();

        $this->bulkIndexProducts($event);
    }

    function it_throws_an_exception_when_attempting_to_bulk_index_a_non_product(
        $normalizer,
        $indexer,
        GenericEvent $event,
        ProductInterface $product,
        \stdClass $aWrongProduct
    ) {
        $event->getSubject()->willReturn([$product, $aWrongProduct]);

        $normalizer->normalize(Argument::cetera())->shouldBeCalledTimes(1);
        $indexer->index(Argument::cetera())->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('bulkIndexProducts', [$event]);
    }

    function it_indexes_a_single_product($normalizer, $indexer, GenericEvent $event, ProductInterface $product)
    {
        $event->getSubject()->willReturn($product);
        $event->hasArgument('unitary')->willReturn(true);
        $event->getArgument('unitary')->willReturn(true);

        $product->getIdentifier()->willReturn('identifier');

        $normalizer->normalize($product, 'indexing')->willReturn(['a key' => 'a value']);
        $indexer->index('an_index_type_for_test_purpose', 'identifier', ['a key' => 'a value'])->shouldBeCalled();

        $this->indexProduct($event);
    }

    function it_bulk_indexes_products(
        $normalizer,
        $indexer,
        GenericEvent $event,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $event->getSubject()->willReturn([$product1, $product2]);

        $product1->getIdentifier()->willReturn('identifier1');
        $product2->getIdentifier()->willReturn('identifier2');

        $normalizer->normalize($product1, 'indexing')->willReturn(['a key' => 'a value']);
        $normalizer->normalize($product2, 'indexing')->willReturn(['a key' => 'another value']);

        $indexer->index('an_index_type_for_test_purpose', 'identifier1', ['a key' => 'a value'])->shouldBeCalled();
        $indexer->index('an_index_type_for_test_purpose', 'identifier2', ['a key' => 'another value'])->shouldBeCalled();

        $this->bulkIndexProducts($event);
    }
}
