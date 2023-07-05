<?php

declare(strict_types=1);

/*
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Pim\Structure\Bundle\EventSubscriber;

use Akeneo\Pim\Structure\Bundle\Event\AttributeOptionWasCreated;
use Akeneo\Pim\Structure\Bundle\Event\AttributeOptionWasUpdated;
use Akeneo\Pim\Structure\Bundle\Event\AttributesOptionWereCreatedOrUpdated;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Messenger\MessageBusInterface;

final class AttributeOptionWasCreatedOrUpdatedSubscriber implements EventSubscriberInterface
{
    /**
     * @var array<string, bool>  $createdAttributesOptionByCode
     */
    private array $createdAttributesOptionByCode;

    public function __construct(
        private readonly FeatureFlag $serenityFeatureFlag,
        private readonly MessageBusInterface $messageBus,
        private readonly LoggerInterface $logger,
        private readonly ?string $tenantId,
        private readonly string $env,
        private readonly int $batchSize = 100,
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::PRE_SAVE => 'recordCreatedAttributeOption',
            StorageEvents::PRE_SAVE_ALL => 'recordCreatedAttributesOption',
            StorageEvents::POST_SAVE => 'dispatchAttributeOptionWasCreatedMessage',
            StorageEvents::POST_SAVE_ALL => 'dispatchAttributesOptionWereCreatedMessage',
        ];
    }

    public function recordCreatedAttributeOption(GenericEvent $event): void
    {
        $attributeOption = $event->getSubject();
        $unitary = $event->getArguments()['unitary'] ?? false;

        if ($unitary === true
            && $attributeOption instanceof AttributeOptionInterface
            && $this->serenityFeatureFlag->isEnabled()
            && $attributeOption->getId() === null
            && !$this->isProdLegacy()
        ) {
            $this->createdAttributesOptionByCode[$this->codes($attributeOption)] = true;
        }
        return;
    }

    public function recordCreatedAttributesOption(GenericEvent $event): void
    {
        $attributesOption = $event->getSubject();

        if (is_array($attributesOption)
            && reset($attributesOption) instanceof AttributeOptionInterface
            && $this->serenityFeatureFlag->isEnabled()
            && !$this->isProdLegacy()
        ) {
            foreach ($attributesOption as $attributeOption) {
                if ($attributeOption->getId() === null && $this->codes($attributeOption) !== null) {
                    $this->createdAttributesOptionByCode[$this->codes($attributeOption)] = true;
                }
            }
        }
        return;
    }

    public function dispatchAttributeOptionWasCreatedMessage(GenericEvent $event): void
    {
        $attributeOption = $event->getSubject();
        $unitary = $event->getArguments()['unitary'] ?? false;

        if ($unitary === true
            && $attributeOption instanceof AttributeOptionInterface
            && $this->serenityFeatureFlag->isEnabled()
            && !$this->isProdLegacy()
        ) {
            try {
                $event = ($this->createdAttributesOptionByCode[$this->codes($attributeOption)] ?? false)
                    ? new AttributeOptionWasCreated($attributeOption->getId(), $attributeOption->getCode(), $attributeOption->getAttribute()?->getCode(), new \DateTimeImmutable())
                    : new AttributeOptionWasUpdated($attributeOption->getId(), $attributeOption->getCode(), $attributeOption->getAttribute()?->getCode(), new \DateTimeImmutable());

                unset($this->createdAttributesOptionByCode[$this->codes($attributeOption)]);

                $this->messageBus->dispatch(new AttributesOptionWereCreatedOrUpdated([$event]));
            } catch (\Throwable $exception) {
                $this->logger->error('Failed to dispatch AttributesOptionWereCreatedOrUpdated from unitary product update', [
                    'attribute_option_code' => $attributeOption->getCode(),
                    'attribute_option_attribute_code' => $attributeOption->getAttribute()?->getCode(),
                    'error' => $exception->getMessage(),
                ]);
            }
        }
    }

    public function dispatchAttributesOptionWereCreatedMessage(GenericEvent $event): void
    {
        $attributesOption = $event->getSubject();
        if (is_array($attributesOption)
            && reset($attributesOption) instanceof AttributeOptionInterface
            && $this->serenityFeatureFlag->isEnabled()
            && !$this->isProdLegacy()
        ) {
            try {
                $events = \array_map(
                    function (AttributeOptionInterface $attributeOption) {
                        $event = ($this->createdAttributesOptionByCode[$this->codes($attributeOption)] ?? false)
                            ? new AttributeOptionWasCreated($attributeOption->getId(), $attributeOption->getCode(), $attributeOption->getAttribute()?->getCode(), new \DateTimeImmutable())
                            : new AttributeOptionWasUpdated($attributeOption->getId(), $attributeOption->getCode(), $attributeOption->getAttribute()?->getCode(), new \DateTimeImmutable());

                        unset($this->createdAttributesOptionByCode[$this->codes($attributeOption)]);

                        return $event;
                    },
                    $attributesOption
                );

                $batchEvents = \array_chunk($events, $this->batchSize);
                foreach ($batchEvents as $events) {
                    $message = new AttributesOptionWereCreatedOrUpdated($events);
                    $this->messageBus->dispatch($message);
                }
            } catch (\Throwable $exception) {
                $this->logger->error('Failed to dispatch AttributesOptionWereCreatedOrUpdated from batch products update', [
                    'error' => $exception->getMessage(),
                ]);
            }
        }
    }

    /**
     * In prod legacy we don't have pubsub topic and subscription, so it would not work.
     *
     * @return bool
     */
    private function isProdLegacy(): bool
    {
        return 'prod' === $this->env && null === $this->tenantId;
    }

    private function codes(AttributeOptionInterface $attributeOption): string
    {
        return $attributeOption->getCode().$attributeOption->getAttribute()?->getCode();
    }
}
