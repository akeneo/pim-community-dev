<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Apps\Model;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class App
{
    private string $id;
    private string $name;
    private array $scopes;
    private string $connectionCode;
    private string $logo;
    private string $author;
    private array $categories;
    private bool $certified;
    private ?string $partner;
    private ?string $externalUrl;

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
    )
    {
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

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    /**
     * @param string[] $scopes
     */
    public function setScopes(array $scopes): self
    {
        $this->scopes = $scopes;
        return $this;
    }

    public function getConnectionCode(): string
    {
        return $this->connectionCode;
    }

    public function setConnectionCode(string $connectionCode): self
    {
        $this->connectionCode = $connectionCode;
        return $this;
    }

    public function getLogo(): string
    {
        return $this->logo;
    }

    public function setLogo(string $logo): self
    {
        $this->logo = $logo;
        return $this;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;
        return $this;
    }

    public function getPartner(): ?string
    {
        return $this->partner;
    }

    public function setPartner(string $partner): self
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * @param string[] $categories
     */
    public function setCategories(array $categories): self
    {
        $this->categories = $categories;
        return $this;
    }

    public function isCertified(): bool
    {
        return $this->certified;
    }

    public function setCertified(bool $certified): self
    {
        $this->certified = $certified;
        return $this;
    }

    public function getExternalUrl(): ?string
    {
        return $this->externalUrl;
    }

    public function setExternalUrl(string $externalUrl): self
    {
        $this->externalUrl = $externalUrl;
        return $this;
    }
}
