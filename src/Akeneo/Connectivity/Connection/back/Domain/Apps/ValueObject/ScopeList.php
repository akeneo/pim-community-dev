<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Apps\ValueObject;

final class ScopeList
{
    private const SCOPE_OPENID = 'openid';

    /**
     * @param array<string> $scopes
     */
    private function __construct(private array $scopes)
    {
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
        $scopes = empty($scopeString) ? [] : explode(' ', $scopeString);

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
     *
     * @param array<string> $scopes
     */
    public function addScopes(array $scopes): self
    {
        return self::fromScopes(array_merge($this->scopes, $scopes));
    }

    public function hasScope(string $scope): bool
    {
        return in_array($scope, $this->scopes);
    }

    public function hasScopeOpenId(): bool
    {
        return $this->hasScope(self::SCOPE_OPENID);
    }

    public function toScopeString(): string
    {
        sort($this->scopes);

        return implode(' ', $this->scopes);
    }

    public function equals(self $scopes): bool
    {
        return count(array_diff($this->scopes, $scopes->scopes)) === 0;
    }
}
