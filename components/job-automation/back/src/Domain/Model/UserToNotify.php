<?php

namespace Akeneo\Platform\JobAutomation\Domain\Model;

final class UserToNotify
{
    public function __construct(
        private string $username,
        private string $email,
    ) {
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
