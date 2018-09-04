<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Domain\Model;

use Webmozart\Assert\Assert;

class ChannelIdentifier
{
    /** @var string */
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

    public function equals(ChannelIdentifier $channelIdentifier): bool
    {
        return $this->channelCode === $channelIdentifier->channelCode;
    }

    public function normalize(): ?string
    {
        return $this->channelCode;
    }
}
