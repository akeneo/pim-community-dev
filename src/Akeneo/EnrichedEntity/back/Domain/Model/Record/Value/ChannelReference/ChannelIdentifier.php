<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Domain\Model\Record\Value\ChannelReference;

use Webmozart\Assert\Assert;

class ChannelIdentifier implements ChannelReferenceInterface
{
    /** @var ?string */
    private $channelCode;

    private function __construct(?string $channelCode)
    {
        Assert::notEmpty($channelCode, 'Channel code should not be empty');

        $this->channelCode = $channelCode;
    }

    public static function fromCode(string $code): self
    {
        return new self($code);
    }

    public static function createEmpty()
    {
        return new self(null);
    }

    public function equals(ChannelReferenceInterface $channelReference): bool
    {
        return $channelReference instanceof ChannelIdentifier &&
            $channelReference->channelCode === $this->channelCode;
    }

    public function normalize(): ?string
    {
        return $this->channelCode;
    }
}
