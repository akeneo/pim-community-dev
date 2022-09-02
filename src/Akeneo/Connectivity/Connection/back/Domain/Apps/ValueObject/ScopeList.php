<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Apps\ValueObject;

final class ScopeList
{
    /**
     * @var string[]
     */
    private array $scopes;

    /**
     * @param array<string> $scopes
     */
    private function __construct(array $scopes)
    {
        $this->scopes = \array_unique($scopes);
        \sort($this->scopes);
    }

    /**
     * @param array<string> $scopes
     */
    public static function fromScopes(array $scopes): self
    {
        return new self($scopes);
    }

    public static function fromScopeString(string $scopeString): self
    {
        $scopes = empty($scopeString) ? [] : \explode(' ', $scopeString);

        return new self($scopes);
    }

    /**
     * @return array<string>
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    /**
     * Return a new ScopeList with the added scopes.
     */
    public function addScopes(self $scopeList): self
    {
        return self::fromScopes(\array_unique(\array_merge($this->scopes, $scopeList->scopes)));
    }

    public function hasScope(string $scope): bool
    {
        return \in_array($scope, $this->scopes);
    }

    public function toScopeString(): string
    {
        return \implode(' ', $this->scopes);
    }

    public function equals(self $scopeList): bool
    {
        return $this->toScopeString() === $scopeList->toScopeString();
    }
}
