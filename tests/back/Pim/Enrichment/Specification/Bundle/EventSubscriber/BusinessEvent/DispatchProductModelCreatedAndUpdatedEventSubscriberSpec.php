<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber\BusinessEvent;

use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\BusinessEvent\DispatchProductModelCreatedAndUpdatedEventSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
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

class DispatchProductModelCreatedAndUpdatedEventSubscriberSpec extends ObjectBehavior
{
    function let(Security $security, MessageBusInterface $messageBus)
    {
        $this->beConstructedWith($security, $messageBus, 10, new NullLogger(), new NullLogger());
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(DispatchProductModelCreatedAndUpdatedEventSubscriber::class);
    }

    function it_returns_subscribed_tech_events(): void
    {
        $this->getSubscribedEvents()->shouldReturn(
            [
                StorageEvents::POST_SAVE => 'createAndDispatchProductModelEvents',
                StorageEvents::POST_SAVE_ALL => 'dispatchBufferedProductModelEvents',
            ]
        );
    }

    function it_dispatches_a_single_product_model_created_event($security)
    {
        $user = new User();
        $user->setUsername('julia');

        $security->getUser()->willReturn($user);

        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $messageBus, 10, new NullLogger(), new NullLogger());

        $productModel = new ProductModel();
        $productModel->setCode('polo_col_mao');

        $this->createAndDispatchProductModelEvents(new GenericEvent($productModel, ['is_new' => true, 'unitary' => true]));

        Assert::assertCount(1, $messageBus->messages);
        Assert::assertContainsOnlyInstancesOf(BulkEventInterface::class, $messageBus->messages);

        /** @var EventInterface[] */
        $events = $messageBus->messages[0]->getEvents();
        Assert::assertCount(1, $events);

        $event = $events[0];
        Assert::assertInstanceOf(ProductModelCreated::class, $event);
        Assert::assertEquals(Author::fromUser($user), $event->getAuthor());
        Assert::assertEquals(['code' => 'polo_col_mao'], $event->getData());
    }

    function it_dispatches_a_single_product_model_updated_event($security)
    {
        $user = new User();
        $user->setUsername('julia');

        $security->getUser()->willReturn($user);

        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $messageBus, 10, new NullLogger(), new NullLogger());

        $productModel = new ProductModel();
        $productModel->setCode('polo_col_mao');

        $this->createAndDispatchProductModelEvents(new GenericEvent($productModel, ['is_new' => false, 'unitary' => true]));

        Assert::assertCount(1, $messageBus->messages);
        Assert::assertContainsOnlyInstancesOf(BulkEventInterface::class, $messageBus->messages);

        /** @var EventInterface[] */
        $events = $messageBus->messages[0]->getEvents();
        Assert::assertCount(1, $events);

        $event = $events[0];
        Assert::assertInstanceOf(ProductModelUpdated::class, $event);
        Assert::assertEquals(Author::fromUser($user), $event->getAuthor());
        Assert::assertEquals(['code' => 'polo_col_mao'], $event->getData());
    }

    function it_dispatches_multiple_product_model_events_in_bulk($security)
    {
        $user = new User();
        $user->setUsername('julia');

        $security->getUser()->willReturn($user);

        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $messageBus, 10, new NullLogger(), new NullLogger());

        $productModel1 = new ProductModel();
        $productModel1->setCode('product_model_code_1');
        $productModel2 = new ProductModel();
        $productModel2->setCode('product_model_code_2');

        $this->createAndDispatchProductModelEvents(new GenericEvent($productModel1, ['is_new' => true, 'unitary' => false]));
        $this->createAndDispatchProductModelEvents(new GenericEvent($productModel2, ['is_new' => false, 'unitary' => false]));
        $this->dispatchBufferedProductModelEvents(new GenericEvent());

        Assert::assertCount(1, $messageBus->messages);
        Assert::assertContainsOnlyInstancesOf(BulkEventInterface::class, $messageBus->messages);

        /** @var EventInterface[] */
        $events = $messageBus->messages[0]->getEvents();
        Assert::assertCount(2, $events);

        $event = $events[0];
        Assert::assertInstanceOf(ProductModelCreated::class, $event);
        Assert::assertEquals(Author::fromUser($user), $event->getAuthor());
        Assert::assertEquals(['code' => 'product_model_code_1'], $event->getData());

        $event = $events[1];
        Assert::assertInstanceOf(ProductModelUpdated::class, $event);
        Assert::assertEquals(Author::fromUser($user), $event->getAuthor());
        Assert::assertEquals(['code' => 'product_model_code_2'], $event->getData());
    }

    function it_dispatches_a_batch_of_product_model_events_once_the_max_bulk_size_is_reached($security)
    {
        $user = new User();
        $user->setUsername('julia');

        $security->getUser()->willReturn($user);

        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $messageBus, 2, new NullLogger(), new NullLogger()); // Bulk size of 2

        $productModel1 = new ProductModel();
        $productModel1->setCode('product_model_code_1');
        $productModel2 = new ProductModel();
        $productModel2->setCode('product_model_code_2');
        $productModel3 = new ProductModel();
        $productModel3->setCode('product_model_code_3');

        $this->createAndDispatchProductModelEvents(new GenericEvent($productModel1, ['is_new' => true, 'unitary' => false]));
        $this->createAndDispatchProductModelEvents(new GenericEvent($productModel2, ['is_new' => false, 'unitary' => false]));
        $this->createAndDispatchProductModelEvents(new GenericEvent($productModel3, ['is_new' => true, 'unitary' => false]));
        $this->dispatchBufferedProductModelEvents(new GenericEvent());

        Assert::assertCount(2, $messageBus->messages);
        Assert::assertContainsOnlyInstancesOf(BulkEventInterface::class, $messageBus->messages);

        /** @var EventInterface[] */
        $events = $messageBus->messages[0]->getEvents();
        Assert::assertCount(2, $events);

        $event = $events[0];
        Assert::assertInstanceOf(ProductModelCreated::class, $event);
        Assert::assertEquals(Author::fromUser($user), $event->getAuthor());
        Assert::assertEquals(['code' => 'product_model_code_1'], $event->getData());

        $event = $events[1];
        Assert::assertInstanceOf(ProductModelUpdated::class, $event);
        Assert::assertEquals(Author::fromUser($user), $event->getAuthor());
        Assert::assertEquals(['code' => 'product_model_code_2'], $event->getData());

        /** @var EventInterface[] */
        $events = $messageBus->messages[1]->getEvents();
        Assert::assertCount(1, $events);

        $event = $events[0];
        Assert::assertInstanceOf(ProductModelCreated::class, $event);
        Assert::assertEquals(Author::fromUser($user), $event->getAuthor());
        Assert::assertEquals(['code' => 'product_model_code_3'], $event->getData());
    }

    function it_only_supports_product_model_event($security)
    {
        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $messageBus, 10, new NullLogger(), new NullLogger());

        $this->createAndDispatchProductModelEvents(new GenericEvent(
            new \stdClass(),
            ['is_new' => false, 'unitary' => true]
        ));

        Assert::assertCount(0, $messageBus->messages);
    }

    function it_does_nothing_if_the_user_is_not_defined($security)
    {
        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $messageBus, 10, new NullLogger(), new NullLogger());

        $productModel = new ProductModel();
        $productModel->setCode('product_model_code');

        $security->getUser()->willReturn(null);

        $this->createAndDispatchProductModelEvents(new GenericEvent($productModel, ['is_new' => false, 'unitary' => true]));

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

        $productModel = new ProductModel();
        $productModel->setCode('product_model_code');

        $this->createAndDispatchProductModelEvents(new GenericEvent(
            $productModel,
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
