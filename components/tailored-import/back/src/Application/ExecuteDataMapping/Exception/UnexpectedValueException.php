<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\Exception;

class UnexpectedValueException extends \InvalidArgumentException
{
    public function __construct(mixed $value, string|array $expectedTypes, string $subject)
    {
        if (is_string($expectedTypes)) {
            $expectedTypes = [$expectedTypes];
        }

        parent::__construct(sprintf(
            '%s only accepts "%s", "%s" given',
            $subject,
            implode(', ', $expectedTypes),
            get_debug_type($value),
        ));
    }
}
