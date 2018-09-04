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
    private $identifier;

    private function __construct(?ChannelIdentifier $identifier)
    {
        $this->identifier = $identifier;
    }

    public static function fromChannelCode(?string $channelCode): self
    {
        // We need to discuss this:
        // I see the point here, but we miss the point of having an explicit/dedicated constructor for the case
        // when there are no reference.
        //
        // In my opinion, it's fine to do have some checkings in the classes instanciating this class
        // to call the right constructor because that's what *we* want, we want to make explicit that it
        // can construct a channel with no reference.
        // while having this kind of constructors *hides* this business case.
        // @see Akeneo/EnrichedEntity/back/Infrastructure/Persistence/Sql/Record/Hydrator/ValueHydrator.php:67
        if (null === $channelCode) {
            return ChannelReference::noReference();
        }

        return self::fromChannelIdentifier(
            ChannelIdentifier::fromCode($channelCode)
        );
    }

    public static function fromChannelIdentifier(ChannelIdentifier $identifier): self
    {
        return new self($identifier) ;
    }

    public static function noReference(): self
    {
        return new self(null);
    }

    public function equals(ChannelReference $channelReference): bool
    {
        if ($channelReference->isEmpty() && $this->isEmpty()) {
            return true;
        }
        if ($channelReference->isEmpty() || $this->isEmpty()) {
            return false;
        }

        return $this->identifier->equals($channelReference->identifier);
    }

    public function getIdentifier(): ChannelIdentifier
    {
        return $this->identifier;
    }

    public function normalize(): ?string
    {
        if (null === $this->identifier) {
            return null;
        }

        return $this->identifier->normalize();
    }

    public function isEmpty(): bool
    {
        return null === $this->identifier;
    }
}
