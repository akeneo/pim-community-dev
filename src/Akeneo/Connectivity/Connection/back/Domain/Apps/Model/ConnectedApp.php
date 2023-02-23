<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Apps\Model;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ConnectedApp
{
    /**
     * @param string[] $scopes
     * @param string[] $categories
     */
    public function __construct(
        private readonly string $id,
        private readonly string $name,
        private readonly array $scopes,
        private readonly string $connectionCode,
        private readonly ?string $logo,
        private readonly ?string $author,
        private readonly string $userGroupName,
        private readonly string $connectionUsername,
        private readonly array $categories = [],
        private readonly bool $certified = false,
        private readonly ?string $partner = null,
        private readonly bool $isCustomApp = false,
        private readonly bool $isPending = false,
        private readonly bool $hasOutdatedScopes = false,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string[]
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function getConnectionCode(): string
    {
        return $this->connectionCode;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function getUserGroupName(): string
    {
        return $this->userGroupName;
    }

    public function getConnectionUsername(): string
    {
        return $this->connectionUsername;
    }

    public function getPartner(): ?string
    {
        return $this->partner;
    }

    /**
     * @return string[]
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    public function isCertified(): bool
    {
        return $this->certified;
    }

    public function isCustomApp(): bool
    {
        return $this->isCustomApp;
    }

    public function isPending(): bool
    {
        return $this->isPending;
    }

    public function hasOutdatedScopes(): bool
    {
        return $this->hasOutdatedScopes;
    }

    /**
     * @return array{
     *  id: string,
     *  name: string,
     *  scopes: array<string>,
     *  connection_code: string,
     *  logo: string|null,
     *  author: string|null,
     *  user_group_name: string,
     *  connection_username: string,
     *  categories: array<string>,
     *  certified: bool,
     *  partner: string|null,
     *  is_custom_app: bool,
     *  is_pending: bool,
     *  has_outdated_scopes: bool
     * }
     */
    public function normalize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'scopes' => $this->scopes,
            'connection_code' => $this->connectionCode,
            'logo' => $this->logo,
            'author' => $this->author,
            'user_group_name' => $this->userGroupName,
            'connection_username' => $this->connectionUsername,
            'categories' => $this->categories,
            'certified' => $this->certified,
            'partner' => $this->partner,
            'is_custom_app' => $this->isCustomApp,
            'is_pending' => $this->isPending,
            'has_outdated_scopes' => $this->hasOutdatedScopes,
        ];
    }

    /**
     * @param array<string> $categories
     */
    public function withUpdatedDescription(
        string $name,
        ?string $logo,
        ?string $author,
        array $categories = [],
        bool $certified = false,
        ?string $partner = null,
    ): self {
        return new self(
            $this->id,
            $name,
            $this->scopes,
            $this->connectionCode,
            $logo,
            $author,
            $this->userGroupName,
            $this->connectionUsername,
            $categories,
            $certified,
            $partner,
            $this->isCustomApp,
            $this->isPending,
            $this->hasOutdatedScopes,
        );
    }
}
