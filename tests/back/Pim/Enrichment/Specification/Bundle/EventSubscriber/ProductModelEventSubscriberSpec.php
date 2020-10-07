<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber;

use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\ProductModelEventSubscriber;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use PHPUnit\Framework\Assert;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\User\UserInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
                StorageEvents::POST_SAVE => ['produceBusinessSaveEvent', 1000],
            ]
        );
    }

    // TODO : API-1309: product model created
    /*
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
        $security->getUser()->willReturn($user);

        $normalizer->normalize($productModel, 'standard')->willReturn(['code' => 'polo_col_mao',]);

        $this->produceBusinessSaveEvent(new GenericEvent($productModel, ['created' => true]));

        Assert::assertCount(1, $messageBus->messages);
    }
    */

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
        $security->getUser()->willReturn($user);

        $normalizer->normalize($productModel, 'standard')->willReturn(['code' => 'polo_col_mao',]);

        $this->produceBusinessSaveEvent(new GenericEvent($productModel));

        Assert::assertCount(1, $messageBus->messages);
    }

    function it_does_not_produce_business_save_event_because_event_subject_is_not_a_product_model(
        $security,
        $normalizer
    ) {
        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $normalizer, $messageBus);

        $result = $this->produceBusinessSaveEvent(new GenericEvent('NOT_A_PRODUCT_MODEL'));

        Assert::assertCount(0, $messageBus->messages);
    }

    function it_does_not_produce_business_save_event_because_there_is_no_logged_user(
        UserInterface $user,
        $security,
        $normalizer
    ) {
        $messageBus = $this->getMessageBus();
        $this->beConstructedWith($security, $normalizer, $messageBus);

        $productModel = new ProductModel();
        $productModel->setCode('polo_col_mao');

        $security->getUser()->willReturn(null);

        $this->shouldThrow(
            new \LogicException('User should not be null.')
        )->during('produceBusinessSaveEvent', [new GenericEvent($productModel)]);
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
