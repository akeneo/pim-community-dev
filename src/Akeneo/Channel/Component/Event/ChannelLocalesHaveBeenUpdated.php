<?php

declare(strict_types=1);

namespace Akeneo\Channel\Component\Event;

use Symfony\Component\EventDispatcher\Event;

final class ChannelLocalesHaveBeenUpdated extends Event
{
    /** @var string */
    private $channelCode;

    /** @var array */
    private $previousLocaleCodes;

    /** @var array */
    private $newLocaleCodes;

    public function __construct(string $channelCode, array $previousLocaleCodes, array $newLocaleCodes)
    {
        sort($previousLocaleCodes);
        sort($newLocaleCodes);

        $this->channelCode = $channelCode;
        $this->previousLocaleCodes = $previousLocaleCodes;
        $this->newLocaleCodes = $newLocaleCodes;
    }

    public function channelCode(): string
    {
        return $this->channelCode;
    }

    public function previousLocaleCodes(): array
    {
        return $this->previousLocaleCodes;
    }

    public function newLocaleCodes(): array
    {
        return $this->newLocaleCodes;
    }

    public function deletedLocaleCodes(): array
    {
        return array_values(
            array_diff($this->previousLocaleCodes, $this->newLocaleCodes)
        );
    }
}
