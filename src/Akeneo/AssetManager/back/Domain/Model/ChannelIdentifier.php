<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Domain\Model;

use Webmozart\Assert\Assert;

class ChannelIdentifier
{
    private string $identifier;

    private function __construct(string $identifier)
    {
        Assert::notEmpty($identifier, 'Channel identifier should not be empty');

        $this->identifier = $identifier;
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
