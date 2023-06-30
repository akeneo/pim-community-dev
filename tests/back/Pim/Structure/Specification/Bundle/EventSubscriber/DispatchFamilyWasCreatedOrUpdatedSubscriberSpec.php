<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Structure\Bundle\EventSubscriber;

use Akeneo\Pim\Structure\Bundle\EventSubscriber\DispatchFamilyWasCreatedOrUpdatedSubscriber;
use Akeneo\Pim\Structure\Component\Event\FamilyWasCreated;
use Akeneo\Pim\Structure\Component\Event\FamilyWasUpdated;
use Akeneo\Pim\Structure\Component\Model\Family;
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
final class DispatchFamilyWasCreatedOrUpdatedSubscriberSpec extends ObjectBehavior
{
    function let(
        FeatureFlag $featureFlag,
        MessageBusInterface $messageBus,
        ClockInterface $clock,
        LoggerInterface $logger,
    ) {
        $this->beConstructedWith($featureFlag, $messageBus, $clock, $logger, 'tenant', 'prod');
        $featureFlag->isEnabled()->willReturn(true);

        $createdFamily = new Family();
        $createdFamily->setCode('created_family');
        $this->beforeSave(new GenericEvent($createdFamily, ['unitary' => true]));

        $createdFamily2 = new Family();
        $createdFamily2->setCode('created_family2');
        $createdFamily3 = new Family();
        $createdFamily3->setCode('created_family3');
        $this->beforeBulkSave(new GenericEvent([$createdFamily2, $createdFamily3]));

        $updatedFamily = new Family();
        $this->setIdOnFamily($updatedFamily, 10);
        $updatedFamily->setCode('updated_family1');
        $this->beforeSave(new GenericEvent($updatedFamily, ['unitary' => true]));

        $updatedFamily2 = new Family();
        $this->setIdOnFamily($updatedFamily2, 10);
        $updatedFamily2->setCode('updated_family2');
        $updatedFamily3 = new Family();
        $this->setIdOnFamily($updatedFamily3, 11);
        $updatedFamily3->setCode('updated_family3');
        $this->beforeBulkSave(new GenericEvent([$updatedFamily2, $updatedFamily3]));
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
        $this->shouldHaveType(DispatchFamilyWasCreatedOrUpdatedSubscriber::class);
    }

    function it_dispatches_information_that_a_product_is_created(
        MessageBusInterface $messageBus,
        ClockInterface $clock
    ): void {
        $family = new Family();
        $this->setIdOnFamily($family, 10);
        $family->setCode('created_family');

        $now = new \DateTimeImmutable();
        $clock->now()->willReturn($now);
        $messageBus->dispatch(new FamilyWasCreated(10, 'created_family', $now))
            ->shouldBeCalledOnce()->willReturn(new Envelope(new \stdClass()));

        $this->onUnitarySave(new GenericEvent($family, ['unitary' => true]));
    }

    function it_dispatches_information_that_a_product_is_updated(
        MessageBusInterface $messageBus,
        ClockInterface $clock
    ): void {
        $family = new Family();
        $this->setIdOnFamily($family, 10);
        $family->setCode('updated_family1');

        $now = new \DateTimeImmutable();
        $clock->now()->willReturn($now);
        $messageBus->dispatch(new FamilyWasUpdated(10, 'updated_family1', $now))
            ->shouldBeCalledOnce()->willReturn(new Envelope(new \stdClass()));

        $this->onUnitarySave(new GenericEvent($family, ['unitary' => true]));
    }

    function it_dispatches_information_that_some_products_are_created(
        MessageBusInterface $messageBus,
        ClockInterface $clock
    ): void {
        $createdFamily2 = new Family();
        $this->setIdOnFamily($createdFamily2, 10);
        $createdFamily2->setCode('created_family2');
        $createdFamily3 = new Family();
        $this->setIdOnFamily($createdFamily3, 11);
        $createdFamily3->setCode('created_family3');

        $now = new \DateTimeImmutable();
        $clock->now()->willReturn($now);
        $messageBus->dispatch(new FamilyWasCreated(10, 'created_family2', $now))
            ->shouldBeCalledOnce()->willReturn(new Envelope(new \stdClass()));
        $messageBus->dispatch(new FamilyWasCreated(11, 'created_family3', $now))
            ->shouldBeCalledOnce()->willReturn(new Envelope(new \stdClass()));

        $this->onBulkSave(new GenericEvent([$createdFamily2, $createdFamily3]));
    }

    function it_dispatches_information_that_some_products_are_updated(
        MessageBusInterface $messageBus,
        ClockInterface $clock
    ): void {
        $updatedFamily2 = new Family();
        $this->setIdOnFamily($updatedFamily2, 10);
        $updatedFamily2->setCode('updated_family2');
        $updatedFamily3 = new Family();
        $this->setIdOnFamily($updatedFamily3, 11);
        $updatedFamily3->setCode('updated_family3');

        $now = new \DateTimeImmutable();
        $clock->now()->willReturn($now);
        $messageBus->dispatch(new FamilyWasUpdated(10, 'updated_family2', $now))
            ->shouldBeCalledOnce()->willReturn(new Envelope(new \stdClass()));
        $messageBus->dispatch(new FamilyWasUpdated(11, 'updated_family3', $now),)
            ->shouldBeCalledOnce()->willReturn(new Envelope(new \stdClass()));

        $this->onBulkSave(new GenericEvent([$updatedFamily2, $updatedFamily3]));
    }

    private function setIdOnFamily(Family $family, int $id): void
    {
        $reflectionClass = new \ReflectionClass(Family::class);
        $reflectionClass->getProperty('id')->setValue($family, $id);
    }
}
