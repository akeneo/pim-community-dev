<?php

declare(strict_types=1);

namespace Akeneo\FreeTrial\Domain\Exception;

final class InvalidEmailException extends InvitationException
{
    public const ERROR_CODE = 'invalid_email';

    public function __construct()
    {
        parent::__construct(self::ERROR_CODE);
    }
}
