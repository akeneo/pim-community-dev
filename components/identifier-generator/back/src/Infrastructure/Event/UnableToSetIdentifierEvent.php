<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Event;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\UnableToSetIdentifierException;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UnableToSetIdentifierEvent
{
    public function __construct(private UnableToSetIdentifierException $exception)
    {
    }

    public function getException(): UnableToSetIdentifierException
    {
        return $this->exception;
    }
}
