<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\EventSubscriber;

use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Structure\Component\Exception\AttributeRemovalException;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CheckAttributeIsNotUsedAsChannelConversionUnitOnDeletion implements EventSubscriberInterface
{
    public function __construct(
        private readonly ChannelRepositoryInterface $channelRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::PRE_REMOVE => 'onPreRemove',
        ];
    }

    public function onPreRemove(RemoveEvent $event): void
    {
        $attribute = $event->getSubject();

        if (!$attribute instanceof AttributeInterface) {
            return;
        }

        if (!$event->hasArgument('unitary') || true !== $event->getArgument('unitary')) {
            return;
        }

        $channelCodes = $this->channelCodesUsedAsConversionUnit($attribute->getCode());
        if (0 < count($channelCodes)) {
            throw new AttributeRemovalException('flash.attribute.used_as_conversion_unit', ['%channelCodes%' => join(', ', $channelCodes)]);
        }
    }

    private function channelCodesUsedAsConversionUnit(string $attributeCode): array
    {
        $channelCodes = [];
        foreach ($this->channelRepository->findAll() as $channel) {
            $attributeCodes = array_keys($channel->getConversionUnits());
            if (in_array($attributeCode, $attributeCodes)) {
                $channelCodes[] = $channel->getCode();
            }
        }

        return $channelCodes;
    }
}
