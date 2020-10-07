<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber;

use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\ProductModelEventSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductModelEventSubscriberSpec extends ObjectBehavior
{
    function let(Security $security, NormalizerInterface $normalizer, MessageBusInterface $messageBus)
    {
        $this->beConstructedWith($security, $normalizer, $messageBus);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(ProductModelEventSubscriber::class);
    }

    function it_returns_subscribed_events(): void
    {
        $this->getSubscribedEvents()->shouldReturn(
            [
                StorageEvents::POST_REMOVE => ['produceBusinessRemoveEvent', 1000],
            ]
        );
    }

    function it_does_produce_business_remove_event(
        UserInterface $user,
        $security,
        $normalizer
    ) {
        $productModel = new ProductModel();
        $productModel->setCode('my_product_model');

        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $normalizer, $messageBus);

        $user->getUsername()->willReturn('julia');
        $security->getUser()->willReturn($user);

        $normalizer->normalize($productModel, 'standard')->willReturn(
            [
                'code' => 'my_product_model',
            ]
        );

        $this->produceBusinessRemoveEvent(new GenericEvent($productModel));

        Assert::assertCount(1, $messageBus->messages);
    }

    function it_does_not_produce_business_remove_event_because_event_subject_is_not_a_product(
        $security,
        $normalizer
    ) {
        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $normalizer, $messageBus);

        $result = $this->produceBusinessRemoveEvent(new GenericEvent('NOT_A_PRODUCT_MODEL'));

        Assert::assertEquals(null, $result->getWrappedObject());
    }

    function it_does_not_produce_business_remove_event_because_there_is_no_logged_user(
        $security,
        $normalizer
    ) {
        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $normalizer, $messageBus);

        $productModel = new ProductModel();
        $productModel->setCode('pm');

        $security->getUser()->willReturn(null);

        $this->shouldThrow(
            new \LogicException('User should not be null.')
        )->during('produceBusinessRemoveEvent', [new GenericEvent($productModel)]);
    }

    private function getMessageBus()
    {
        return new class() implements MessageBusInterface {

            public $messages = [];

            public function dispatch($message, array $stamps = []): Envelope
            {
                $this->messages[] = $message;

                return new Envelope($message);
            }
        };
    }
}
