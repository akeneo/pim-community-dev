<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\EventSubscriber;

use Akeneo\Pim\Structure\Component\Event\FamilyWasCreated;
use Akeneo\Pim\Structure\Component\Event\FamilyWasUpdated;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DispatchFamilyWasCreatedOrUpdatedSubscriber implements EventSubscriberInterface
{
    /** @var array<string, bool> */
    private array $createdFamiliesByCode = [];

    public function __construct(
        private readonly FeatureFlag $serenityFeatureFlag,
        private readonly FeatureFlag $dqiUcsEventFeatureFlag,
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
            $familyCode = $subject->getCode();
            $this->createdFamiliesByCode[$familyCode] = true;
        }
    }

    public function onUnitarySave(GenericEvent $event): void
    {
        if ($this->shouldSkipUnitaryEvent($event)) {
            return;
        }

        $subject = $event->getSubject();
        $event = \array_key_exists($subject->getCode(), $this->createdFamiliesByCode)
            ? new FamilyWasCreated(
                $subject->getId(),
                $subject->getCode(),
                $this->clock->now()
            )
            : new FamilyWasUpdated(
                $subject->getId(),
                $subject->getCode(),
                $this->clock->now()
            )
        ;
        try {
            $this->messageBus->dispatch($event);
        } catch (\Throwable $e) {
            // Catch any error to not block the critical path
            $this->logger->error('Failed to dispatch FamilyWasCreated/FamilyWasUpdated from unitary save', [
                'entity_id' => $subject->getId(),
                'entity_code' => $subject->getCode(),
                'error' => $e->getMessage(),
            ]);
        }
        unset($this->createdFamiliesByCode[$subject->getCode()]);
    }

    public function beforeBulkSave(GenericEvent $event): void
    {
        if ($this->shouldSkipBulkEvent($event)) {
            return;
        }

        $subjects = $event->getSubject();
        foreach ($subjects as $family) {
            if (null === $family->getId()) {
                $familyCode = $family->getCode();
                $this->createdFamiliesByCode[$familyCode] = $familyCode;
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
        try {
            foreach ($subjects as $family) {
                $event = \array_key_exists($family->getCode(), $this->createdFamiliesByCode)
                    ? new FamilyWasCreated(
                        $family->getId(),
                        $family->getCode(),
                        $now
                    )
                    : new FamilyWasUpdated(
                        $family->getId(),
                        $family->getCode(),
                        $now
                    )
                ;
                $this->messageBus->dispatch($event);
                unset($this->createdFamiliesByCode[$family->getCode()]);
            }
        } catch (\Throwable $e) {
            // Catch any error to not block the critical path
            $this->logger->error('Failed to dispatch FamilyWasCreated/FamilyWasUpdated from batch save', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function shouldSkipUnitaryEvent(GenericEvent $event): bool
    {
        $subject = $event->getSubject();

        return !$this->serenityFeatureFlag->isEnabled()
            || !$this->dqiUcsEventFeatureFlag->isEnabled()
            || $this->isProdLegacy()
            || !$subject instanceof FamilyInterface
            || !$event->hasArgument('unitary')
            || false === $event->getArgument('unitary')
        ;
    }

    private function shouldSkipBulkEvent(GenericEvent $event): bool
    {
        $subjects = $event->getSubject();

        return !$this->serenityFeatureFlag->isEnabled()
            || !$this->dqiUcsEventFeatureFlag->isEnabled()
            || $this->isProdLegacy()
            || !\is_array($subjects)
            || [] === $subjects
            || !current($subjects) instanceof FamilyInterface
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
