<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber\BusinessEvent;

use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\BusinessEvent\DispatchProductRemovedEventSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Model\UserInterface;
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
        $this->beConstructedWith($security, $messageBus);
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
            ]
        );
    }

    function it_does_produce_business_remove_event(
        UserInterface $user,
        $security
    ) {
        $product = new Product();
        $product->setIdentifier('product_identifier');

        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $messageBus);

        $user = new User();
        $user->setUsername('julia');
        $security->getUser()->willReturn($user);

        $this->createAndDispatchProductEvents(new GenericEvent($product));

        Assert::assertCount(1, $messageBus->messages);
    }

    function it_does_not_produce_business_remove_event_because_event_subject_is_not_a_product(
        $security
    ) {
        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $messageBus);

        $this->createAndDispatchProductEvents(new GenericEvent('NOT_A_PRODUCT'));

        Assert::assertCount(0, $messageBus->messages);
    }

    function it_does_not_produce_business_remove_event_because_there_is_no_logged_user(
        $security
    ) {
        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $messageBus);

        $product = new Product();
        $product->setIdentifier('product_identifier');

        $security->getUser()->willReturn(null);

        // $this->shouldThrow(
        //     new \LogicException('User should not be null.')
        // )->during('createAndDispatchProductEvents', [new GenericEvent($product)]);

        // TODO: https://akeneo.atlassian.net/browse/CXP-443
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
