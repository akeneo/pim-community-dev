<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\back\Domain\Repository;

use Throwable;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
