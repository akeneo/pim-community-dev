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

namespace Akeneo\Tool\Component\Messenger\Stamp;

use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * Used to send headers in a message.
 * Decoding received messages to a CustomHeaderStamp is not supported.
 */
interface CustomHeaderStamp extends StampInterface
{
    public function header(): string;
    public function value(): string;
}
