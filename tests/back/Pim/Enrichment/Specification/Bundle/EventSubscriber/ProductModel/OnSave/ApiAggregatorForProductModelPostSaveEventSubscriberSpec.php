<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber\ProductModel\OnSave;

use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\ProductModel\OnSave\ApiAggregatorForProductModelPostSaveEventSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ApiAggregatorForProductModelPostSaveEventSubscriberSpec extends ObjectBehavior
{
    function let(EventDispatcherInterface $dispatcher)
    {
        $this->beConstructedWith($dispatcher);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ApiAggregatorForProductModelPostSaveEventSubscriber::class);
    }

    function it_is_a_batch_event_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_subscribes_to_post_save_events()
    {
        $this->getSubscribedEvents()->shouldHaveKey(StorageEvents::POST_SAVE);
    }

    function it_batch_the_events(GenericEvent $event)
    {
        $this->activate();

        $productModel = new ProductModel();
        $productModel->setCode('pm1');
        $event->getSubject()->willReturn($productModel);
        $event->getArguments()->willReturn(['unitary' => true]);
        $event->setArgument('unitary', false)->shouldBeCalled();
        $event->stopPropagation()->shouldNotBeCalled();
        $this->batchEvents($event);
        $this->getEventProductModels()->shouldHaveCount(1);

        $productModel = new ProductModel();
        $productModel->setCode('pm2');
        $event = new GenericEvent($productModel, ['unitary' => true]);
        $this->batchEvents($event);
        $this->getEventProductModels()->shouldHaveCount(2);
    }

    function it_does_not_batch_if_not_activated(GenericEvent $event)
    {
        $productModel = new ProductModel();
        $event->getSubject()->willReturn($productModel);
        $event->getArguments()->willReturn(['unitary' => true]);
        $event->setArgument(Argument::cetera())->shouldNotBeCalled();
        $event->stopPropagation()->shouldNotBeCalled();

        $this->batchEvents($event);
        $this->getEventProductModels()->shouldHaveCount(0);
    }

    function it_does_not_batch_if_deactivated(GenericEvent $event)
    {
        $productModel = new ProductModel();
        $this->activate();
        $this->deactivate();
        $event->getSubject()->willReturn($productModel);
        $event->getArguments()->willReturn(['unitary' => true]);
        $event->setArgument(Argument::cetera())->shouldNotBeCalled();
        $event->stopPropagation()->shouldNotBeCalled();

        $this->batchEvents($event);
        $this->getEventProductModels()->shouldHaveCount(0);
    }

    function it_does_not_batch_if_not_unitary(GenericEvent $event)
    {
        $this->activate();

        $productModel = new ProductModel();
        $productModel->setCode('pm1');
        $event->getSubject()->willReturn($productModel);
        $event->getArguments()->willReturn(['unitary' => false]);
        $event->setArgument(Argument::cetera())->shouldNotBeCalled();
        $event->stopPropagation()->shouldNotBeCalled();

        $this->batchEvents($event);
        $this->getEventProductModels()->shouldHaveCount(0);

        $event = new GenericEvent($productModel);
        $this->batchEvents($event);
        $this->getEventProductModels()->shouldHaveCount(0);
    }

    function it_does_not_batch_if_not_a_product(GenericEvent $event)
    {
        $this->activate();

        $object = new \stdClass();
        $event->getSubject()->willReturn($object);
        $event->getArguments()->willReturn(['unitary' => true]);
        $event->setArgument(Argument::cetera())->shouldNotBeCalled();
        $event->stopPropagation()->shouldNotBeCalled();

        $this->batchEvents($event);
        $this->getEventProductModels()->shouldHaveCount(0);
    }

    function it_dispatches_bulk_event(EventDispatcherInterface $dispatcher)
    {
        $this->activate();

        $productModel = new ProductModel();
        $productModel->setCode('pm1');
        $event = new GenericEvent($productModel, ['unitary' => true]);
        $this->batchEvents($event);
        $this->getEventProductModels()->shouldHaveCount(1);

        $productModel = new ProductModel();
        $productModel->setCode('pm2');
        $event = new GenericEvent($productModel, ['unitary' => true]);
        $this->batchEvents($event);
        $this->getEventProductModels()->shouldHaveCount(2);

        $dispatcher->dispatch(Argument::cetera())->shouldBeCalled();
        $this->dispatchAllEvents();
        $this->getEventProductModels()->shouldHaveCount(0);
    }
}
