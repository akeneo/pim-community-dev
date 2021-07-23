<?php

declare(strict_types=1);

namespace Akeneo\FreeTrial\Domain\Exception;

final class InvitationFailedException extends InvitationException
{
    public const ERROR_CODE = 'invitation_failed';

    public function __construct()
    {
        parent::__construct(self::ERROR_CODE);
    }
}
