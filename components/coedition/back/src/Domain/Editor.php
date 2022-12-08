<?php

namespace Akeneo\CoEdition\Domain;

use Akeneo\CoEdition\Domain\ValueObject\EditorToken;

class Editor
{
    public function __construct(
        private readonly EditorToken $token,
        private readonly string $name,
        private readonly string $avatar,
    )
    {

    }

    public function getToken(): EditorToken
    {
        return $this->token;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAvatar(): string
    {
        return $this->avatar;
    }

}
