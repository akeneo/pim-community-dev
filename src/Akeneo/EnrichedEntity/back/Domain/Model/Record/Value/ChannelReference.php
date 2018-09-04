<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Domain\Model\Record\Value;

use Akeneo\EnrichedEntity\Domain\Model\ChannelIdentifier;

/**
 * A ChannelReference expresses a link to a channel.
 *
 * If there is one, then the channel reference it is wrapping a ChannelIdentifier
 * If it has no link then it is null
 *
 * @see ChannelIdentifier
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ChannelReference
{
    /** @var ChannelIdentifier|null */
    private $channelIdentifier;

    private function __construct(?ChannelIdentifier $channelIdentifier)
    {
        $this->channelIdentifier = $channelIdentifier;
    }

    public static function fromChannelIdentifier(ChannelIdentifier $identifier): self
    {
        return new self($identifier) ;
    }

    public static function noChannel(): self
    {
        return new self(null) ;
    }

    public function equals(ChannelReference $channelReference): bool
    {
        if (null === $channelReference->channelIdentifier && null === $this->channelIdentifier) {
            return true;
        }
        if (null === $channelReference->channelIdentifier || null === $this->channelIdentifier) {
            return false;
        }

        return $this->channelIdentifier->equals($channelReference->channelIdentifier);
    }

    public function normalize(): ?string
    {
        if (null === $this->channelIdentifier) {
            return null;
        }

        return $this->channelIdentifier->normalize();
    }

    public function isEmpty(): bool
    {
        return null === $this->channelIdentifier;
    }
}
