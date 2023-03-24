<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\EventSubscriber;

use Akeneo\Channel\API\Query\Channel;
use Akeneo\Channel\API\Query\FindChannels;
use Akeneo\Pim\Structure\Component\Exception\CannotRemoveAttributeException;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CheckAttributeIsNotUsedAsChannelConversionUnitOnDeletionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly FindChannels $findChannels,
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

        $channelCodes = $this->channelCodesUsedAsConversionUnit($attribute->getCode());
        if (0 < count($channelCodes)) {
            throw new CannotRemoveAttributeException('flash.attribute.used_as_conversion_unit', ['%channelCodes%' => join(', ', $channelCodes)]);
        }
    }

    private function channelCodesUsedAsConversionUnit(string $attributeCode): array
    {
        $channels = $this->findChannels->findAll();

        $channelsUsedAsConversionUnit = array_filter(
            $channels,
            static fn (Channel $channel): bool => $channel->getConversionUnits()->hasConversionUnit($attributeCode)
        );

        return array_map(
            static fn (Channel $channel): string => $channel->getCode(),
            $channelsUsedAsConversionUnit,
        );
    }
}
