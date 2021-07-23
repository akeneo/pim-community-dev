<?php

declare(strict_types=1);

namespace Akeneo\FreeTrial\Domain\Exception;

final class InvitationAlreadySentException extends InvitationException
{
    public const ERROR_CODE = 'invitation_already_sent';

    public function __construct()
    {
        parent::__construct(self::ERROR_CODE);
    }
}
