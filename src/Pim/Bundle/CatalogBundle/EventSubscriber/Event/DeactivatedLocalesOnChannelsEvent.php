<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\EventSubscriber\Event;

use Symfony\Component\EventDispatcher\Event;

class DeactivatedLocalesOnChannelsEvent extends Event
{
    public const NAME = 'deactivated_locales_on_channel';

    /** @var array */
    private $deactivatedLocalesOnChannels;

    public function __construct(array $deactivatedLocalesOnChannels)
    {
        $this->deactivatedLocalesOnChannels = $deactivatedLocalesOnChannels;
    }

    public function deactivatedLocalesOnChannels(): array
    {
        $this->deactivatedLocalesOnChannels;
    }
}
