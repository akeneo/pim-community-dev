<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\EnrichedEntity\back\Domain\Repository;

use Throwable;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EntityNotFoundException extends \RuntimeException
{
    public static function withIdentifier(string $type, string $identifier): self
    {
        $message = sprintf(
            'Could not find entity of type "%s" with identifier "%s"',
            $type,
            $identifier
        );

        return new self($message);
    }
}
