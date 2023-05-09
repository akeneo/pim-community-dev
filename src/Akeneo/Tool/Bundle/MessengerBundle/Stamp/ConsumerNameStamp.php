<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2023 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Tool\Bundle\MessengerBundle\Stamp;

use Symfony\Component\Messenger\Stamp\StampInterface;

final class ConsumerNameStamp implements StampInterface
{
    public function __construct(public readonly string $consumerName)
    {
    }

    public function __toString(): string
    {
        return $this->consumerName;
    }
}
