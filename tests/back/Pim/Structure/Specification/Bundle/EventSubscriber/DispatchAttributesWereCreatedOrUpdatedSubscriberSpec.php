<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Structure\Bundle\EventSubscriber;

use Akeneo\Pim\Structure\Bundle\EventSubscriber\DispatchAttributesWereCreatedOrUpdatedSubscriber;
use Akeneo\Pim\Structure\Component\Event\AttributesWereCreatedOrUpdated;
use Akeneo\Pim\Structure\Component\Event\AttributeWasCreated;
use Akeneo\Pim\Structure\Component\Event\AttributeWasUpdated;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PhpSpec\ObjectBehavior;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DispatchAttributesWereCreatedOrUpdatedSubscriberSpec extends ObjectBehavior
{
    function let(FeatureFlag $featureFlag, MessageBusInterface $messageBus, ClockInterface $clock, LoggerInterface $logger)
    {
        $this->beConstructedWith($featureFlag, $messageBus, $clock, $logger, 'tenant', 'prod');
        $featureFlag->isEnabled()->willReturn(true);

        $createdAttribute = new Attribute();
        $createdAttribute->setCode('created_attribute');
        $this->beforeSave(new GenericEvent($createdAttribute, ['unitary' => true]));

        $createdAttribute2 = new Attribute();
        $createdAttribute2->setCode('created_attribute2');
        $createdAttribute3 = new Attribute();
        $createdAttribute3->setCode('created_attribute3');
        $this->beforeBulkSave(new GenericEvent([$createdAttribute2, $createdAttribute3]));

        $updatedAttribute = new Attribute();
        $updatedAttribute->setId(10);
        $updatedAttribute->setCode('updated_attribute1');
        $this->beforeSave(new GenericEvent($updatedAttribute, ['unitary' => true]));

        $updatedAttribute2 = new Attribute();
        $updatedAttribute2->setId(10);
        $updatedAttribute2->setCode('updated_attribute2');
        $updatedAttribute3 = new Attribute();
        $updatedAttribute3->setId(11);
        $updatedAttribute3->setCode('updated_attribute3');
        $this->beforeBulkSave(new GenericEvent([$updatedAttribute2, $updatedAttribute3]));
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
        $this->shouldHaveType(DispatchAttributesWereCreatedOrUpdatedSubscriber::class);
    }

    function it_dispatches_information_that_a_product_is_created(
        MessageBusInterface $messageBus,
        ClockInterface $clock
    ): void {
        $attribute = new Attribute();
        $attribute->setId(10);
        $attribute->setCode('created_attribute');

        $now = new \DateTimeImmutable();
        $clock->now()->willReturn($now);
        $messageBus->dispatch(new AttributesWereCreatedOrUpdated([
            new AttributeWasCreated(
                10,
                'created_attribute',
                $now
            ),
        ]))->shouldBeCalledOnce()->willReturn(new Envelope(new \stdClass()));

        $this->onUnitarySave(new GenericEvent($attribute, ['unitary' => true]));
    }

    function it_dispatches_information_that_a_product_is_updated(
        MessageBusInterface $messageBus,
        ClockInterface $clock
    ): void {
        $attribute = new Attribute();
        $attribute->setId(10);
        $attribute->setCode('updated_attribute1');

        $now = new \DateTimeImmutable();
        $clock->now()->willReturn($now);
        $messageBus->dispatch(new AttributesWereCreatedOrUpdated([
            new AttributeWasUpdated(
                10,
                'updated_attribute1',
                $now
            ),
        ]))->shouldBeCalledOnce()->willReturn(new Envelope(new \stdClass()));

        $this->onUnitarySave(new GenericEvent($attribute, ['unitary' => true]));
    }

    function it_dispatches_information_that_some_products_are_created(
        MessageBusInterface $messageBus,
        ClockInterface $clock
    ): void {
        $createdAttribute2 = new Attribute();
        $createdAttribute2->setId(10);
        $createdAttribute2->setCode('created_attribute2');
        $createdAttribute3 = new Attribute();
        $createdAttribute3->setId(11);
        $createdAttribute3->setCode('created_attribute3');

        $now = new \DateTimeImmutable();
        $clock->now()->willReturn($now);
        $messageBus->dispatch(new AttributesWereCreatedOrUpdated([
            new AttributeWasCreated(
                10,
                'created_attribute2',
                $now
            ),
            new AttributeWasCreated(
                11,
                'created_attribute3',
                $now
            ),
        ]))->shouldBeCalledOnce()->willReturn(new Envelope(new \stdClass()));

        $this->onBulkSave(new GenericEvent([$createdAttribute2, $createdAttribute3]));
    }

    function it_dispatches_information_that_some_products_are_updated(
        MessageBusInterface $messageBus,
        ClockInterface $clock
    ): void {
        $updatedAttribute2 = new Attribute();
        $updatedAttribute2->setId(10);
        $updatedAttribute2->setCode('updated_attribute2');
        $updatedAttribute3 = new Attribute();
        $updatedAttribute3->setId(11);
        $updatedAttribute3->setCode('updated_attribute3');

        $now = new \DateTimeImmutable();
        $clock->now()->willReturn($now);
        $messageBus->dispatch(new AttributesWereCreatedOrUpdated([
            new AttributeWasUpdated(
                10,
                'updated_attribute2',
                $now
            ),
            new AttributeWasUpdated(
                11,
                'updated_attribute3',
                $now
            ),
        ]))->shouldBeCalledOnce()->willReturn(new Envelope(new \stdClass()));

        $this->onBulkSave(new GenericEvent([$updatedAttribute2, $updatedAttribute3]));
    }
}
