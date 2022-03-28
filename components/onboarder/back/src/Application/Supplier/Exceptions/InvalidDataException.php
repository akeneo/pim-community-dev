<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Application\Supplier\Exceptions;

final class InvalidDataException extends \Exception
{
    public function __construct(iterable $violations)
    {
    }
}
