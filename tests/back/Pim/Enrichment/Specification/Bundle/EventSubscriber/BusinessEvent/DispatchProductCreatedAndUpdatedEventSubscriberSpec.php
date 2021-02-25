<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber\BusinessEvent;

use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\BusinessEvent\DispatchProductCreatedAndUpdatedEventSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\BulkEventInterface;
use Akeneo\Platform\Component\EventQueue\EventInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Akeneo\UserManagement\Component\Model\User;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;

class DispatchProductCreatedAndUpdatedEventSubscriberSpec extends ObjectBehavior
{
    function let(Security $security, MessageBusInterface $messageBus)
    {
        $this->beConstructedWith($security, $messageBus, 10, new NullLogger(), new NullLogger());
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(DispatchProductCreatedAndUpdatedEventSubscriber::class);
    }

    function it_returns_subscribed_tech_events(): void
    {
        $this->getSubscribedEvents()->shouldReturn(
            [
                StorageEvents::POST_SAVE => 'createAndDispatchProductEvents',
                StorageEvents::POST_SAVE_ALL => 'dispatchBufferedProductEvents',
            ]
        );
    }

    function it_dispatches_a_single_product_created_event($security)
    {
        $user = new User();
        $user->setUsername('julia');

        $security->getUser()->willReturn($user);

        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $messageBus, 10, new NullLogger(), new NullLogger());

        $product = new Product();
        $product->setIdentifier('product_identifier');

        $this->createAndDispatchProductEvents(new GenericEvent($product, ['is_new' => true, 'unitary' => true]));

        Assert::assertCount(1, $messageBus->messages);
        Assert::assertContainsOnlyInstancesOf(BulkEventInterface::class, $messageBus->messages);

        /** @var EventInterface[] */
        $events = $messageBus->messages[0]->getEvents();
        Assert::assertCount(1, $events);

        $event = $events[0];
        Assert::assertInstanceOf(ProductCreated::class, $event);
        Assert::assertEquals(Author::fromUser($user), $event->getAuthor());
        Assert::assertEquals(['identifier' => 'product_identifier'], $event->getData());
    }

    function it_dispatches_a_single_product_updated_event($security)
    {
        $user = new User();
        $user->setUsername('julia');

        $security->getUser()->willReturn($user);

        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $messageBus, 10, new NullLogger(), new NullLogger());

        $product = new Product();
        $product->setIdentifier('product_identifier');

        $this->createAndDispatchProductEvents(new GenericEvent($product, ['is_new' => false, 'unitary' => true]));

        Assert::assertCount(1, $messageBus->messages);
        Assert::assertContainsOnlyInstancesOf(BulkEventInterface::class, $messageBus->messages);

        /** @var EventInterface[] */
        $events = $messageBus->messages[0]->getEvents();
        Assert::assertCount(1, $events);

        $event = $events[0];
        Assert::assertInstanceOf(ProductUpdated::class, $event);
        Assert::assertEquals(Author::fromUser($user), $event->getAuthor());
        Assert::assertEquals(['identifier' => 'product_identifier'], $event->getData());
    }

    function it_dispatches_multiple_product_events_in_bulk($security)
    {
        $user = new User();
        $user->setUsername('julia');

        $security->getUser()->willReturn($user);

        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $messageBus, 10, new NullLogger(), new NullLogger());

        $product1 = new Product();
        $product1->setIdentifier('product_identifier_1');
        $product2 = new Product();
        $product2->setIdentifier('product_identifier_2');

        $this->createAndDispatchProductEvents(new GenericEvent($product1, ['is_new' => true, 'unitary' => false]));
        $this->createAndDispatchProductEvents(new GenericEvent($product2, ['is_new' => false, 'unitary' => false]));
        $this->dispatchBufferedProductEvents(new GenericEvent());

        Assert::assertCount(1, $messageBus->messages);
        Assert::assertContainsOnlyInstancesOf(BulkEventInterface::class, $messageBus->messages);

        /** @var EventInterface[] */
        $events = $messageBus->messages[0]->getEvents();
        Assert::assertCount(2, $events);

        $event = $events[0];
        Assert::assertInstanceOf(ProductCreated::class, $event);
        Assert::assertEquals(Author::fromUser($user), $event->getAuthor());
        Assert::assertEquals(['identifier' => 'product_identifier_1'], $event->getData());

        $event = $events[1];
        Assert::assertInstanceOf(ProductUpdated::class, $event);
        Assert::assertEquals(Author::fromUser($user), $event->getAuthor());
        Assert::assertEquals(['identifier' => 'product_identifier_2'], $event->getData());
    }

    function it_dispatches_a_batch_of_product_events_once_the_max_bulk_size_is_reached($security)
    {
        $user = new User();
        $user->setUsername('julia');

        $security->getUser()->willReturn($user);

        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $messageBus, 2, new NullLogger(), new NullLogger()); // Bulk size of 2

        $product1 = new Product();
        $product1->setIdentifier('product_identifier_1');
        $product2 = new Product();
        $product2->setIdentifier('product_identifier_2');
        $product3 = new Product();
        $product3->setIdentifier('product_identifier_3');

        $this->createAndDispatchProductEvents(new GenericEvent($product1, ['is_new' => true, 'unitary' => false]));
        $this->createAndDispatchProductEvents(new GenericEvent($product2, ['is_new' => false, 'unitary' => false]));
        $this->createAndDispatchProductEvents(new GenericEvent($product3, ['is_new' => true, 'unitary' => false]));
        $this->dispatchBufferedProductEvents(new GenericEvent());

        Assert::assertCount(2, $messageBus->messages);
        Assert::assertContainsOnlyInstancesOf(BulkEventInterface::class, $messageBus->messages);

        /** @var EventInterface[] */
        $events = $messageBus->messages[0]->getEvents();
        Assert::assertCount(2, $events);

        $event = $events[0];
        Assert::assertInstanceOf(ProductCreated::class, $event);
        Assert::assertEquals(Author::fromUser($user), $event->getAuthor());
        Assert::assertEquals(['identifier' => 'product_identifier_1'], $event->getData());

        $event = $events[1];
        Assert::assertInstanceOf(ProductUpdated::class, $event);
        Assert::assertEquals(Author::fromUser($user), $event->getAuthor());
        Assert::assertEquals(['identifier' => 'product_identifier_2'], $event->getData());

        /** @var EventInterface[] */
        $events = $messageBus->messages[1]->getEvents();
        Assert::assertCount(1, $events);

        $event = $events[0];
        Assert::assertInstanceOf(ProductCreated::class, $event);
        Assert::assertEquals(Author::fromUser($user), $event->getAuthor());
        Assert::assertEquals(['identifier' => 'product_identifier_3'], $event->getData());
    }

    function it_only_supports_product_event($security)
    {
        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $messageBus, 10, new NullLogger(), new NullLogger());

        $this->createAndDispatchProductEvents(new GenericEvent(
            new \stdClass(),
            ['is_new' => false, 'unitary' => true]
        ));

        Assert::assertCount(0, $messageBus->messages);
    }

    function it_does_nothing_if_the_user_is_not_defined($security)
    {
        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $messageBus, 10, new NullLogger(), new NullLogger());

        $product = new Product();
        $product->setIdentifier('product_identifier');

        $security->getUser()->willReturn(null);

        $this->createAndDispatchProductEvents(new GenericEvent(
            $product,
            ['is_new' => false, 'unitary' => true]
        ));

        Assert::assertCount(0, $messageBus->messages);
    }

    function it_does_nothing_if_the_save_has_been_forced($security)
    {
        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $messageBus, 10, new NullLogger(), new NullLogger());

        $product = new Product();
        $product->setIdentifier('product_identifier');

        $this->createAndDispatchProductEvents(new GenericEvent(
            $product,
            ['is_new' => false, 'force_save' => true]
        ));
        Assert::assertCount(0, $messageBus->messages);
    }

    function it_logs_an_error_if_the_event_bus_transport_raise_an_exception($security, LoggerInterface $logger)
    {
        $messageBus = new class () implements MessageBusInterface
        {
            public function dispatch($message, array $stamps = []): Envelope
            {
                throw new TransportException('An error occured');
            }
        };
        $this->beConstructedWith($security, $messageBus, 10, $logger, new NullLogger(), new NullLogger());

        $user = new User();
        $user->setUsername('julia');
        $security->getUser()->willReturn($user);

        $product = new Product();
        $product->setIdentifier('product_identifier');

        $this->createAndDispatchProductEvents(new GenericEvent(
            $product,
            ['is_new' => false, 'unitary' => true]
        ));

        $logger->critical('An error occured')->shouldBeCalled();
    }

    private function getMessageBus(): MessageBusInterface
    {
        return new class () implements MessageBusInterface
        {
            public $messages = [];

            public function dispatch($message, array $stamps = []): Envelope
            {
                $this->messages[] = $message;

                return new Envelope($message);
            }
        };
    }
}
