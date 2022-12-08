<?php

namespace Akeneo\CoEdition\Application\Builder;

use Akeneo\CoEdition\Domain\Editor;
use Akeneo\CoEdition\Domain\ValueObject\EditorToken;
use Webmozart\Assert\Assert;

class EditorBuilder
{
    private ?EditorToken $token;
    private string $name;
    private string $avatar;

    public function __construct()
    {
        $this->token = null;
        $this->name = '';
        $this->avatar = '';
    }

    public function withToken(EditorToken $token): self
    {
        $this->token = $token;
        return $this;
    }

    public function withName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function withAvatar(string $avatar): self
    {
        $this->avatar = $avatar;
        return $this;
    }

    public function build(): Editor
    {
        Assert::isInstanceOf($this->token, EditorToken::class, 'The editor token must be provided');

        return new Editor(
            token:  $this->token,
            name: $this->name,
            avatar: $this->avatar,
        );
    }
}
