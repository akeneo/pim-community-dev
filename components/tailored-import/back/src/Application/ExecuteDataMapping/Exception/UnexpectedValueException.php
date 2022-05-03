<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\Exception;

class UnexpectedValueException extends \InvalidArgumentException
{
    public function __construct(mixed $value, string $expectedType, string $subject)
    {
        parent::__construct(sprintf('%s only accepts "%s", "%s" given', $subject, $expectedType, get_debug_type($value)));
    }
}
