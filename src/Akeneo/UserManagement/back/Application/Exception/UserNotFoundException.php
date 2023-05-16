<?php

namespace Akeneo\UserManagement\Application\Exception;

class UserNotFoundException extends \RuntimeException
{
    public function __construct(int $identifier)
    {
        parent::__construct(sprintf('Username with id "%s" not found', $identifier));
    }
}
