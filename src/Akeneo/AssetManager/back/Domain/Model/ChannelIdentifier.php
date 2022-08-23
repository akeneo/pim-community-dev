<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Domain\Model;

use Webmozart\Assert\Assert;

class ChannelIdentifier
{
    private function __construct(private string $identifier)
    {
        Assert::stringNotEmpty($identifier, 'Channel identifier should not be empty');
    }

    public static function fromCode(string $identifier): self
    {
        return new self($identifier);
    }

    public function equals(ChannelIdentifier $channelIdentifier): bool
    {
        return $this->identifier === $channelIdentifier->identifier;
    }

    public function normalize(): string
    {
        return $this->identifier;
    }
}
