<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\EventSubscriber\Event;

use Symfony\Component\EventDispatcher\Event;

class DeactivatedLocalesOnChannel
{
    /** @var int */
    private $channelId;

    /** @var array */
    private $localeIds;

    public function __construct(int $channelId, array $localeIds)
    {
        $this->channelId = $channelId;
        $this->localeIds = $localeIds;
    }

    public function channelId(): int
    {
        return $this->channelId;
    }

    public function localeIds(): array
    {
        return $this->localeIds;
    }
}
