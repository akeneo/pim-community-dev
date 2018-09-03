<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Domain\Model\Record\Value\ChannelReference;

/**
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class NoChannel implements ChannelReferenceInterface
{
    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public function equals(ChannelReferenceInterface $channelReference): bool
    {
        return $channelReference instanceof NoChannel;
    }

    public function normalize(): ?string
    {
        return null;
    }
}
