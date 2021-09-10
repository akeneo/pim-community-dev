<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Apps\Model;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class App
{
    private string $id;
    private string $name;
    private string $connectionCode;
    private string $logo;
    private string $author;
    /** @var string[] $scopes */
    private array $scopes;
    /** @var string[] $categories */
    private array $categories;
    private bool $certified;
    private ?string $partner;
    private ?string $externalUrl;

    /**
     * @param string[] $scopes
     * @param string[] $categories
     */
    public function __construct(
        string $id,
        string $name,
        array $scopes,
        string $connectionCode,
        string $logo,
        string $author,
        array $categories = [],
        bool $certified = false,
        string $partner = null,
        string $externalUrl = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->scopes = $scopes;
        $this->connectionCode = $connectionCode;
        $this->logo = $logo;
        $this->author = $author;
        $this->categories = $categories;
        $this->certified = $certified;
        $this->partner = $partner;
        $this->externalUrl = $externalUrl;
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

    public function getLogo(): string
    {
        return $this->logo;
    }

    public function getAuthor(): string
    {
        return $this->author;
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

    public function getExternalUrl(): ?string
    {
        return $this->externalUrl;
    }
}
