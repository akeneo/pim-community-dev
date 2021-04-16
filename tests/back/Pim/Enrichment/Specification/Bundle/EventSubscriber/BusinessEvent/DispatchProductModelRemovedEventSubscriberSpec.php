<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber\BusinessEvent;

use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\BusinessEvent\DispatchBufferedPimEventSubscriberInterface;
use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\BusinessEvent\DispatchProductModelRemovedEventSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\BulkEventInterface;
use Akeneo\Platform\Component\EventQueue\EventInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;

class DispatchProductModelRemovedEventSubscriberSpec extends ObjectBehavior
{
    function let(
        Security $security,
        MessageBusInterface $messageBus
    ) {
        $this->beConstructedWith($security, $messageBus, 10, new NullLogger(), new NullLogger());
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(DispatchProductModelRemovedEventSubscriber::class);
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

    function it_dispatches_a_single_product_model_removed_event(
        UserInterface $user,
        $security
    ) {
        $user = new User();
        $user->setUsername('julia');

        $security->getUser()->willReturn($user);

        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $messageBus, 10, new NullLogger(), new NullLogger());

        $productModel = new ProductModel();
        $productModel->setCode('jean');

        $this->createAndDispatchPimEvents(new GenericEvent($productModel, ['unitary' => true]));

        Assert::assertCount(1, $messageBus->messages);
        Assert::assertContainsOnlyInstancesOf(BulkEventInterface::class, $messageBus->messages);

        /** @var EventInterface[] */
        $events = $messageBus->messages[0]->getEvents();
        Assert::assertCount(1, $events);

        $event = $events[0];
        Assert::assertInstanceOf(ProductModelRemoved::class, $event);
        Assert::assertEquals(Author::fromUser($user), $event->getAuthor());
        Assert::assertEquals(
            [
                'code' => 'jean',
                'category_codes' => []
            ],
            $event->getData()
        );
    }

    function it_dispatches_multiple_product_model_removed_events_in_bulk($security)
    {
        $user = new User();
        $user->setUsername('julia');

        $security->getUser()->willReturn($user);

        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $messageBus, 10, new NullLogger(), new NullLogger());

        $product1 = new ProductModel();
        $product1->setCode('t-shirt');
        $product2 = new ProductModel();
        $product2->setCode('jean');

        $this->createAndDispatchPimEvents(new GenericEvent($product1, ['unitary' => false]));
        $this->createAndDispatchPimEvents(new GenericEvent($product2, ['unitary' => false]));
        $this->dispatchBufferedPimEvents(new GenericEvent());

        Assert::assertCount(1, $messageBus->messages);
        Assert::assertContainsOnlyInstancesOf(BulkEventInterface::class, $messageBus->messages);

        /** @var EventInterface[] */
        $events = $messageBus->messages[0]->getEvents();
        Assert::assertCount(2, $events);

        $event = $events[0];
        Assert::assertInstanceOf(ProductModelRemoved::class, $event);
        Assert::assertEquals(Author::fromUser($user), $event->getAuthor());
        Assert::assertEquals(
            [
                'code' => 't-shirt',
                'category_codes' => []
            ],
            $event->getData()
        );

        $event = $events[1];
        Assert::assertInstanceOf(ProductModelRemoved::class, $event);
        Assert::assertEquals(Author::fromUser($user), $event->getAuthor());
        Assert::assertEquals(
            [
                'code' => 'jean',
                'category_codes' => []
            ],
            $event->getData()
        );
    }

    function it_only_supports_product_model_event(
        $security
    ) {
        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $messageBus, 10, new NullLogger(), new NullLogger());

        $this->createAndDispatchPimEvents(new GenericEvent(
            new \stdClass(),
            ['unitary' => true]
        ));

        Assert::assertCount(0, $messageBus->messages);
    }

    function it_does_nothing_if_the_user_is_not_defined(
        $security
    ) {
        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $messageBus, 10, new NullLogger(), new NullLogger());

        $productModel = new ProductModel();
        $productModel->setCode('product_model_code');

        $security->getUser()->willReturn(null);

        $this->createAndDispatchPimEvents(new GenericEvent(
            $productModel,
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

        $productModel = new ProductModel();
        $productModel->setCode('product_model_code');

        $this->createAndDispatchPimEvents(new GenericEvent(
            $productModel,
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
