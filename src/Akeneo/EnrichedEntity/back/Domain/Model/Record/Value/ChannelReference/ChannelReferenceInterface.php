<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Domain\Model\Record\Value\ChannelReference;

/**
 *
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
interface ChannelReferenceInterface
{
    public function equals(ChannelReferenceInterface $channelReference): bool;
}
