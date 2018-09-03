<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\back\Domain\Model;

use Webmozart\Assert\Assert;

class ChannelIdentifier
{
    private $channelCode;

    private function __construct(string $channelCode)
    {
        Assert::notEmpty($channelCode, 'Channel code should not be empty');

        $this->channelCode = $channelCode;
    }

    public static function fromCode(string $code): self
    {
        return new self($code);
    }

    public function normalize(): string
    {
        return $this->channelCode;
    }
}
