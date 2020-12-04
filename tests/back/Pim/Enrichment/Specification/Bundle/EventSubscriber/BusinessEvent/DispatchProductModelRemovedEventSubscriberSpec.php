<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber\BusinessEvent;

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
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;

class DispatchProductModelRemovedEventSubscriberSpec extends ObjectBehavior
{
    function let(
        Security $security,
        MessageBusInterface $messageBus
    ) {
        $this->beConstructedWith($security, $messageBus, 10);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(DispatchProductModelRemovedEventSubscriber::class);
    }

    function it_returns_subscribed_events(): void
    {
        $this->getSubscribedEvents()->shouldReturn(
            [
                StorageEvents::POST_REMOVE => 'createAndDispatchProductModelEvents',
                StorageEvents::POST_SAVE_ALL => 'dispatchBufferedProductModelEvents',
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
        $this->beConstructedWith($security, $messageBus, 10);

        $productModel = new ProductModel();
        $productModel->setCode('jean');

        $this->createAndDispatchProductModelEvents(new GenericEvent($productModel, ['unitary' => true]));

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
        $this->beConstructedWith($security, $messageBus, 10);

        $product1 = new ProductModel();
        $product1->setCode('t-shirt');
        $product2 = new ProductModel();
        $product2->setCode('jean');

        $this->createAndDispatchProductModelEvents(new GenericEvent($product1, ['unitary' => false]));
        $this->createAndDispatchProductModelEvents(new GenericEvent($product2, ['unitary' => false]));
        $this->dispatchBufferedProductModelEvents(new GenericEvent());

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
        $this->beConstructedWith($security, $messageBus, 10);

        $this->createAndDispatchProductModelEvents(new GenericEvent('NOT_A_PRODUCT'));

        Assert::assertCount(0, $messageBus->messages);
    }

    function it_does_nothing_if_the_user_is_not_defined(
        $security
    ) {
        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $messageBus, 10);

        $productModel = new ProductModel();
        $productModel->setCode('product_model_code');

        $security->getUser()->willReturn(null);

        // $this->shouldThrow(
        //     new \LogicException('User should not be null.')
        // )->during('createAndDispatchProductModelEvents', [new GenericEvent($productModel)]);

        // TODO: https://akeneo.atlassian.net/browse/CXP-443
        $this->createAndDispatchProductModelEvents(new GenericEvent($productModel));
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
