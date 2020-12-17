<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber\BusinessEvent;

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
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;

class DispatchProductRemovedEventSubscriberSpec extends ObjectBehavior
{
    function let(
        Security $security,
        MessageBusInterface $messageBus
    ) {
        $this->beConstructedWith($security, $messageBus, 10);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(DispatchProductRemovedEventSubscriber::class);
    }

    function it_returns_subscribed_events(): void
    {
        $this->getSubscribedEvents()->shouldReturn(
            [
                StorageEvents::POST_REMOVE => 'createAndDispatchProductEvents',
                StorageEvents::POST_SAVE_ALL => 'dispatchBufferedProductEvents',
            ]
        );
    }

    function it_dispatches_a_single_product_removed_event($security)
    {
        $user = new User();
        $user->setUsername('julia');

        $security->getUser()->willReturn($user);

        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $messageBus, 10);

        $product = new Product();
        $product->setIdentifier('blue_jean');

        $this->createAndDispatchProductEvents(new GenericEvent($product, ['unitary' => true]));

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
                'category_codes' => []
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
        $this->beConstructedWith($security, $messageBus, 10);

        $product1 = new Product();
        $product1->setIdentifier('product_identifier_1');
        $product2 = new Product();
        $product2->setIdentifier('product_identifier_2');

        $this->createAndDispatchProductEvents(new GenericEvent($product1, ['unitary' => false]));
        $this->createAndDispatchProductEvents(new GenericEvent($product2, ['unitary' => false]));
        $this->dispatchBufferedProductEvents(new GenericEvent());

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
                'category_codes' => []
            ],
            $event->getData()
        );

        $event = $events[1];
        Assert::assertInstanceOf(ProductRemoved::class, $event);
        Assert::assertEquals(Author::fromUser($user), $event->getAuthor());
        Assert::assertEquals(
            [
                'identifier' => 'product_identifier_2',
                'category_codes' => []
            ],
            $event->getData()
        );
    }

    function it_only_supports_product_removed_event($security)
    {
        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $messageBus, 10);

        $this->createAndDispatchProductEvents(new GenericEvent(new \stdClass()));

        Assert::assertCount(0, $messageBus->messages);
    }

    function it_does_nothing_if_the_user_is_not_defined($security)
    {
        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $messageBus, 10);

        $product = new Product();
        $product->setIdentifier('product_identifier');

        $security->getUser()->willReturn(null);

        $this->createAndDispatchProductEvents(new GenericEvent($product));

        Assert::assertCount(0, $messageBus->messages);
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
