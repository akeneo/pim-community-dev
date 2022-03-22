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
        private string $id,
        private string $name,
        private array $scopes,
        private string $connectionCode,
        private ?string $logo,
        private ?string $author,
        private string $userGroupName,
        private array $categories = [],
        private bool $certified = false,
        private ?string $partner = null,
        private bool $isTestApp = false,
        private bool $isPending = false,
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

    public function isTestApp(): bool
    {
        return $this->isTestApp;
    }

    public function isPending(): bool
    {
        return $this->isPending;
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
     *  categories: array<string>,
     *  certified: bool,
     *  partner: string|null
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
            'categories' => $this->categories,
            'certified' => $this->certified,
            'partner' => $this->partner,
            'is_test_app' => $this->isTestApp,
            'is_pending' => $this->isPending,
        ];
    }
}
