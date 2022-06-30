<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber\BusinessEvent;

use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\BusinessEvent\DispatchBufferedPimEventSubscriberInterface;
use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\BusinessEvent\DispatchProductRemovedEventSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\BulkEventInterface;
use Akeneo\Platform\Component\EventQueue\EventInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Akeneo\UserManagement\Component\Model\User;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;

class DispatchProductRemovedEventSubscriberSpec extends ObjectBehavior
{
    function let(
        Security $security,
        MessageBusInterface $messageBus
    ) {
        $this->beConstructedWith($security, $messageBus, 10, new NullLogger(), new NullLogger());
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(DispatchProductRemovedEventSubscriber::class);
        $this->shouldImplement(DispatchBufferedPimEventSubscriberInterface::class);
    }

    function it_returns_subscribed_events(): void
    {
        $this->getSubscribedEvents()->shouldReturn(
            [
                StorageEvents::POST_REMOVE => 'createAndDispatchPimEvents',
                StorageEvents::POST_REMOVE_ALL => 'dispatchBufferedPimEvents',
            ]
        );
    }

    function it_dispatches_a_single_product_removed_event($security)
    {
        $user = new User();
        $user->setUsername('julia');

        $security->getUser()->willReturn($user);

        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $messageBus, 10, new NullLogger(), new NullLogger());

        $product = new Product();
        $product->setIdentifier('blue_jean');

        $this->createAndDispatchPimEvents(new GenericEvent($product, ['unitary' => true]));

        Assert::assertCount(1, $messageBus->messages);
        Assert::assertContainsOnlyInstancesOf(BulkEventInterface::class, $messageBus->messages);

        /** @var EventInterface[] */
        $events = $messageBus->messages[0]->getEvents();
        Assert::assertCount(1, $events);

        $event = $events[0];
        Assert::assertInstanceOf(ProductRemoved::class, $event);
        Assert::assertEquals(Author::fromUser($user), $event->getAuthor());
        Assert::assertEquals(
            [
                'identifier' => 'blue_jean',
                'uuid' => $product->getUuid(),
                'category_codes' => [],
            ],
            $event->getData()
        );
    }

    function it_dispatches_multiple_product_removed_events_in_bulk($security)
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

        $this->createAndDispatchPimEvents(new GenericEvent($product1, ['unitary' => false]));
        $this->createAndDispatchPimEvents(new GenericEvent($product2, ['unitary' => false]));
        $this->dispatchBufferedPimEvents(new GenericEvent());

        Assert::assertCount(1, $messageBus->messages);
        Assert::assertContainsOnlyInstancesOf(BulkEventInterface::class, $messageBus->messages);

        /** @var EventInterface[] */
        $events = $messageBus->messages[0]->getEvents();
        Assert::assertCount(2, $events);

        $event = $events[0];
        Assert::assertInstanceOf(ProductRemoved::class, $event);
        Assert::assertEquals(Author::fromUser($user), $event->getAuthor());
        Assert::assertEquals(
            [
                'identifier' => 'product_identifier_1',
                'uuid' => $product1->getUuid(),
                'category_codes' => [],
            ],
            $event->getData()
        );

        $event = $events[1];
        Assert::assertInstanceOf(ProductRemoved::class, $event);
        Assert::assertEquals(Author::fromUser($user), $event->getAuthor());
        Assert::assertEquals(
            [
                'identifier' => 'product_identifier_2',
                'uuid' => $product2->getUuid(),
                'category_codes' => [],
            ],
            $event->getData()
        );
    }

    function it_only_supports_product_removed_event($security)
    {
        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $messageBus, 10, new NullLogger(), new NullLogger());

        $this->createAndDispatchPimEvents(new GenericEvent(
            new \stdClass(),
            ['unitary' => true]
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

        $this->createAndDispatchPimEvents(new GenericEvent(
            $product,
            ['unitary' => true]
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
        $this->beConstructedWith($security, $messageBus, 10, $logger, new NullLogger());

        $user = new User();
        $user->setUsername('julia');
        $security->getUser()->willReturn($user);

        $product = new Product();
        $product->setIdentifier('product_identifier');

        $this->createAndDispatchPimEvents(new GenericEvent(
            $product,
            ['unitary' => true]
        ));

        $logger->critical('An error occured')->shouldBeCalled();
    }

    private function getMessageBus()
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
