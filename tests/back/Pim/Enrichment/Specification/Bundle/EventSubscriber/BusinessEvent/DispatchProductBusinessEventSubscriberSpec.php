<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber\BusinessEvent;

use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\BusinessEvent\DispatchProductBusinessEventSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DispatchProductBusinessEventSubscriberSpec extends ObjectBehavior
{
    function let(Security $security, NormalizerInterface $normalizer, MessageBusInterface $messageBus)
    {
        $this->beConstructedWith($security, $normalizer, $messageBus);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(DispatchProductBusinessEventSubscriber::class);
    }

    function it_returns_subscribed_events(): void
    {
        $this->getSubscribedEvents()->shouldReturn(
            [
                StorageEvents::POST_SAVE => ['produceBusinessSaveEvent', 1000],
                StorageEvents::POST_REMOVE => ['produceBusinessRemoveEvent', 1000],
            ]
        );
    }

    function it_produces_a_product_created_event(
        UserInterface $user,
        $security,
        $normalizer
    ) {
        $product = new Product();
        $product->setIdentifier('product_identifier');

        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $normalizer, $messageBus);

        $user->getUsername()->willReturn('julia');
        $security->getUser()->willReturn($user);

        $normalizer->normalize($product, 'standard')->willReturn(
            [
                'identifier' => 'product_identifier',
                'categories' => ['a_category'],
            ]
        );

        $this->produceBusinessSaveEvent(new GenericEvent($product, ['created' => true]));

        Assert::assertCount(1, $messageBus->messages);
    }

    function it_produces_a_product_updated_event(
        UserInterface $user,
        $security,
        $normalizer
    ) {
        $product = new Product();
        $product->setIdentifier('product_identifier');

        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $normalizer, $messageBus);

        $user->getUsername()->willReturn('julia');
        $security->getUser()->willReturn($user);

        $normalizer->normalize($product, 'standard')->willReturn(
            [
                'identifier' => 'product_identifier',
                'categories' => ['a_category'],
            ]
        );

        $this->produceBusinessSaveEvent(new GenericEvent($product, ['updated' => true]));

        Assert::assertCount(1, $messageBus->messages);
    }

    function it_does_not_produce_business_save_event_because_event_subject_is_not_a_product(
        $security,
        $normalizer
    ) {
        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $normalizer, $messageBus);

        $this->produceBusinessSaveEvent(new GenericEvent('NOT_A_PRODUCT', ['updated' => true]));

        Assert::assertCount(0, $messageBus->messages);
    }

    function it_does_not_produce_business_save_event_because_there_is_no_logged_user(
        $security,
        $normalizer
    ) {
        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $normalizer, $messageBus);

        $product = new Product();
        $product->setIdentifier('product_identifier');

        $security->getUser()->willReturn(null);

        $this->shouldThrow(
            new \LogicException('User should not be null.')
        )->during('produceBusinessSaveEvent', [new GenericEvent($product, ['created' => true])]);
    }

    function it_does_produce_business_remove_event(
        UserInterface $user,
        $security,
        $normalizer
    ) {
        $product = new Product();
        $product->setIdentifier('product_identifier');

        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $normalizer, $messageBus);

        $user->getUsername()->willReturn('julia');
        $security->getUser()->willReturn($user);

        $normalizer->normalize($product, 'standard')->willReturn(
            [
                'identifier' => 'product_identifier',
                'categories' => ['a_category'],
            ]
        );

        $this->produceBusinessRemoveEvent(new GenericEvent($product));

        Assert::assertCount(1, $messageBus->messages);
    }

    function it_does_not_produce_business_remove_event_because_event_subject_is_not_a_product(
        $security,
        $normalizer
    ) {
        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $normalizer, $messageBus);

        $this->produceBusinessRemoveEvent(new GenericEvent('NOT_A_PRODUCT'));

        Assert::assertCount(0, $messageBus->messages);
    }

    function it_does_not_produce_business_remove_event_because_there_is_no_logged_user(
        $security,
        $normalizer
    ) {
        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $normalizer, $messageBus);

        $product = new Product();
        $product->setIdentifier('product_identifier');

        $security->getUser()->willReturn(null);

        $this->shouldThrow(
            new \LogicException('User should not be null.')
        )->during('produceBusinessRemoveEvent', [new GenericEvent($product)]);
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
