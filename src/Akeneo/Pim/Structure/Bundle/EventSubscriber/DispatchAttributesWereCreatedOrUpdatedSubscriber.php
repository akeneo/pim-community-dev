<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\EventSubscriber;

use Akeneo\Pim\Structure\Component\Event\AttributesWereCreatedOrUpdated;
use Akeneo\Pim\Structure\Component\Event\AttributeWasCreated;
use Akeneo\Pim\Structure\Component\Event\AttributeWasUpdated;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Today we send messages when we are in serenity, because we have only one consumer
 * that is available in Serenity only.
 * Sending messages when there is no one to consume is not relevant, and it increases the cost of storage
 *
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DispatchAttributesWereCreatedOrUpdatedSubscriber implements EventSubscriberInterface
{
    /** @var array<string, string> */
    private array $createdAttributesByCode = [];

    public function __construct(
        private readonly FeatureFlag $serenityFeatureFlag,
        private readonly MessageBusInterface $messageBus,
        private readonly ClockInterface $clock,
        private readonly LoggerInterface $logger,
        private readonly ?string $tenantId,
        private readonly string $env
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::PRE_SAVE => 'beforeSave',
            StorageEvents::PRE_SAVE_ALL => 'beforeBulkSave',
            StorageEvents::POST_SAVE => 'onUnitarySave',
            StorageEvents::POST_SAVE_ALL => 'onBulkSave',
        ];
    }

    public function beforeSave(GenericEvent $event): void
    {
        if ($this->shouldSkipUnitaryEvent($event)) {
            return;
        }

        $subject = $event->getSubject();
        if (null === $subject->getId()) {
            $attributeCode = $subject->getCode();
            $this->createdAttributesByCode[$attributeCode] = $attributeCode;
        }
    }

    public function onUnitarySave(GenericEvent $event): void
    {
        if ($this->shouldSkipUnitaryEvent($event)) {
            return;
        }

        $subject = $event->getSubject();
        $event = \array_key_exists($subject->getCode(), $this->createdAttributesByCode)
            ? new AttributeWasCreated(
                $subject->getId(),
                $subject->getCode(),
                $this->clock->now()
            )
            : new AttributeWasUpdated(
                $subject->getId(),
                $subject->getCode(),
                $this->clock->now()
            )
        ;
        try {
            $this->messageBus->dispatch(new AttributesWereCreatedOrUpdated([$event]));
        } catch (\Throwable $e) {
            // Catch any error to not block the critical path
            $this->logger->error('Failed to dispatch AttributesWereCreatedOrUpdated from unitary save', [
                'id' => $subject->getId(),
                'code' => $subject->getCode(),
                'error' => $e->getMessage(),
            ]);
        }
        unset($this->createdAttributesByCode[$subject->getCode()]);
    }

    public function beforeBulkSave(GenericEvent $event): void
    {
        if ($this->shouldSkipBulkEvent($event)) {
            return;
        }

        $subjects = $event->getSubject();
        foreach ($subjects as $attribute) {
            if (null === $attribute->getId()) {
                $attributeCode = $attribute->getCode();
                $this->createdAttributesByCode[$attributeCode] = $attributeCode;
            }
        }
    }

    public function onBulkSave(GenericEvent $event): void
    {
        if ($this->shouldSkipBulkEvent($event)) {
            return;
        }

        $subjects = $event->getSubject();
        $now = $this->clock->now();
        $attributeWasCreatedOrUpdatedList = [];
        foreach ($subjects as $attribute) {
            $attributeWasCreatedOrUpdatedList[] = \array_key_exists($attribute->getCode(), $this->createdAttributesByCode)
                ? new AttributeWasCreated(
                    $attribute->getId(),
                    $attribute->getCode(),
                    $now
                )
                : new AttributeWasUpdated(
                    $attribute->getId(),
                    $attribute->getCode(),
                    $now
                )
            ;
            unset($this->createdAttributesByCode[$attribute->getCode()]);
        }

        try {
            $this->messageBus->dispatch(new AttributesWereCreatedOrUpdated($attributeWasCreatedOrUpdatedList));
        } catch (\Throwable $e) {
            // Catch any error to not block the critical path
            $this->logger->error('Failed to dispatch AttributesWereCreatedOrUpdated from batch save', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function shouldSkipUnitaryEvent(GenericEvent $event): bool
    {
        $subject = $event->getSubject();

        return !$this->serenityFeatureFlag->isEnabled()
            || $this->isProdLegacy()
            || !$subject instanceof AttributeInterface
            || !$event->hasArgument('unitary')
            || false === $event->getArgument('unitary')
            ;
    }

    private function shouldSkipBulkEvent(GenericEvent $event): bool
    {
        $subjects = $event->getSubject();

        return !$this->serenityFeatureFlag->isEnabled()
            || $this->isProdLegacy()
            || !\is_array($subjects)
            || [] === $subjects
            || !current($subjects) instanceof AttributeInterface
            ;
    }

    /**
     * In prod legacy we don't have pubsub topic and subscription, so it does not work.
     */
    private function isProdLegacy(): bool
    {
        return 'prod' === $this->env && null === $this->tenantId;
    }
}
