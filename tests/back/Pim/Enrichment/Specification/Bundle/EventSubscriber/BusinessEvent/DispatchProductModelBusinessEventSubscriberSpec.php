<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber\BusinessEvent;

use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\BusinessEvent\DispatchProductModelBusinessEventSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DispatchProductModelBusinessEventSubscriberSpec extends ObjectBehavior
{
    function let(
        Security $security,
        NormalizerInterface $normalizer,
        MessageBusInterface $messageBus
    ) {
        $this->beConstructedWith($security, $normalizer, $messageBus);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(DispatchProductModelBusinessEventSubscriber::class);
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

    function it_produces_a_product_model_created_event(
        UserInterface $user,
        $security,
        $normalizer
    ) {
        $productModel = new ProductModel();
        $productModel->setCode('polo_col_mao');

        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $normalizer, $messageBus);

        $user->getUsername()->willReturn('julia');
        $user->isApiUser()->willReturn(false);
        $security->getUser()->willReturn($user);

        $normalizer->normalize($productModel, 'standard')->willReturn(
            [
                'code' => 'polo_col_mao',
                'categories' => ['a_category'],
            ]
        );

        $this->produceBusinessSaveEvent(new GenericEvent($productModel, ['is_new' => true]));

        Assert::assertCount(1, $messageBus->messages);
        Assert::assertInstanceOf(ProductModelCreated::class, $messageBus->messages[0]);
    }

    function it_produces_a_product_model_updated_event(
        UserInterface $user,
        $security,
        $normalizer
    ) {
        $productModel = new ProductModel();
        $productModel->setCode('polo_col_mao');

        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $normalizer, $messageBus);

        $user->getUsername()->willReturn('julia');
        $user->isApiUser()->willReturn(false);
        $security->getUser()->willReturn($user);

        $normalizer->normalize($productModel, 'standard')->willReturn(
            [
                'code' => 'polo_col_mao',
                'categories' => ['a_category'],
            ]
        );

        $this->produceBusinessSaveEvent(new GenericEvent($productModel));

        Assert::assertCount(1, $messageBus->messages);
        Assert::assertInstanceOf(ProductModelUpdated::class, $messageBus->messages[0]);
    }

    function it_does_not_produce_business_save_event_because_event_subject_is_not_a_product_model(
        $security,
        $normalizer
    ) {
        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $normalizer, $messageBus);

        $this->produceBusinessSaveEvent(new GenericEvent('NOT_A_PRODUCT_MODEL'));

        Assert::assertCount(0, $messageBus->messages);
    }

    function it_does_not_produce_business_save_event_because_there_is_no_logged_user(
        $security,
        $normalizer
    ) {
        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $normalizer, $messageBus);

        $productModel = new ProductModel();
        $productModel->setCode('polo_col_mao');

        $security->getUser()->willReturn(null);

        // $this->shouldThrow(
        //     new \LogicException('User should not be null.')
        // )->during('produceBusinessSaveEvent', [new GenericEvent($productModel)]);

        // TODO: https://akeneo.atlassian.net/browse/CXP-443
        $this->produceBusinessSaveEvent(new GenericEvent($productModel));
        Assert::assertCount(0, $messageBus->messages);
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
        $user->isApiUser()->willReturn(false);
        $security->getUser()->willReturn($user);

        $normalizer->normalize($productModel, 'standard')->willReturn(
            [
                'code' => 'my_product_model',
                'categories' => ['a_category'],
            ]
        );

        $this->produceBusinessRemoveEvent(new GenericEvent($productModel));

        Assert::assertCount(1, $messageBus->messages);
        Assert::assertInstanceOf(ProductModelRemoved::class, $messageBus->messages[0]);
    }

    function it_does_not_produce_business_remove_event_because_event_subject_is_not_a_product(
        $security,
        $normalizer
    ) {
        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $normalizer, $messageBus);

        $this->produceBusinessRemoveEvent(new GenericEvent('NOT_A_PRODUCT_MODEL'));

        Assert::assertCount(0, $messageBus->messages);
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

        // $this->shouldThrow(
        //     new \LogicException('User should not be null.')
        // )->during('produceBusinessRemoveEvent', [new GenericEvent($productModel)]);

        // TODO: https://akeneo.atlassian.net/browse/CXP-443
        $this->produceBusinessRemoveEvent(new GenericEvent($productModel));
        Assert::assertCount(0, $messageBus->messages);
    }

    private function getMessageBus()
    {
        return new class () implements MessageBusInterface {

            public $messages = [];

            public function dispatch($message, array $stamps = []): Envelope
            {
                $this->messages[] = $message;

                return new Envelope($message);
            }
        };
    }
}
