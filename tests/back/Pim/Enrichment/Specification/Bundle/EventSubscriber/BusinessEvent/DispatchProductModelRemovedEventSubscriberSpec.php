<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber\BusinessEvent;

use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\BusinessEvent\DispatchProductModelRemovedEventSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
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
        $this->beConstructedWith($security, $messageBus);
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
            ]
        );
    }

    function it_dispatches_a_single_product_model_removed_event(
        UserInterface $user,
        $security
    ) {
        $productModel = new ProductModel();
        $productModel->setCode('product_identifier');

        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $messageBus);

        $user = new User();
        $user->setUsername('julia');
        $security->getUser()->willReturn($user);

        $this->createAndDispatchProductModelEvents(new GenericEvent($productModel));

        Assert::assertCount(1, $messageBus->messages);
    }

    function it_only_supports_product_model_event(
        $security
    ) {
        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $messageBus);

        $this->createAndDispatchProductModelEvents(new GenericEvent('NOT_A_PRODUCT'));

        Assert::assertCount(0, $messageBus->messages);
    }

    function it_does_nothing_if_the_user_is_not_defined(
        $security
    ) {
        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $messageBus);

        $productModel = new ProductModel();
        $productModel->setCode('product_model_identifier');

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
