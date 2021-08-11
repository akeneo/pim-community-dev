<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Marketplace\Model;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class App
{
    private string $id;
    private string $name;
    private string $logo;
    private string $author;
    private ?string $partner;
    private ?string $description;
    private string $url;
    private bool $certified;
    /** @var array<string> */
    private array $categories;
    private string $activateUrl;
    private string $callbackUrl;

    private const REQUIRED_KEYS = [
        'id',
        'name',
        'logo',
        'author',
        'url',
        'categories',
        'activate_url',
        'callback_url',
    ];

    private function __construct()
    {
    }

    /**
     * @param array{
     *     id: string,
     *     name: string,
     *     logo: string,
     *     author: string,
     *     partner?: string,
     *     description?: string,
     *     url: string,
     *     categories: array<string>,
     *     certified?: bool,
     *     activate_url: string,
     *     callback_url: string,
     * } $values
     */
    public static function fromWebMarketplaceValues(array $values): self
    {
        foreach (self::REQUIRED_KEYS as $key) {
            if (!isset($values[$key])) {
                throw new \InvalidArgumentException(sprintf('Missing property "%s" in given app', $key));
            }
        }

        $self = new self();

        $self->id = $values['id'];
        $self->name = $values['name'];
        $self->logo = $values['logo'];
        $self->author = $values['author'];
        $self->partner = $values['partner'] ?? null;
        $self->description = $values['description'] ?? null;
        $self->url = $values['url'];
        $self->categories = $values['categories'];
        $self->certified = $values['certified'] ?? false;
        $self->activateUrl = $values['activate_url'];
        $self->callbackUrl = $values['callback_url'];

        return $self;
    }

    /**
     * @param array<string> $queryParameters
     */
    public function withAnalytics(array $queryParameters): self
    {
        $values = $this->normalize();
        $values['url'] = static::appendQueryParametersToUrl($values['url'], $queryParameters);

        /* @phpstan-ignore-next-line */
        return self::fromWebMarketplaceValues($values);
    }

    /**
     * @param array<string> $queryParameters
     */
    public function withPimUrlSource(array $queryParameters): self
    {
        $values = $this->normalize();
        $values['activate_url'] = static::appendQueryParametersToUrl($values['activate_url'], $queryParameters);

        /* @phpstan-ignore-next-line */
        return self::fromWebMarketplaceValues($values);
    }

    /**
     * @param array<string> $queryParameters
     */
    private static function appendQueryParametersToUrl(string $url, array $queryParameters): string
    {
        $query = http_build_query($queryParameters);

        if (parse_url($url, PHP_URL_QUERY)) {
            $url = sprintf('%s&%s', $url, $query);
        } else {
            $url = sprintf('%s?%s', $url, $query);
        }

        return $url;
    }

    /**
     * @return array{
     *  id: string,
     *  name: string,
     *  logo: string,
     *  author: string,
     *  partner: string|null,
     *  description: string|null,
     *  url: string,
     *  categories: array<string>,
     *  certified: bool,
     *  activate_url: string,
     *  callback_url: string,
     * }
     */
    public function normalize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'logo' => $this->logo,
            'author' => $this->author,
            'partner' => $this->partner,
            'description' => $this->description,
            'url' => $this->url,
            'categories' => $this->categories,
            'certified' => $this->certified,
            'activate_url' => $this->activateUrl,
            'callback_url' => $this->callbackUrl,
        ];
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

    public function setPartner(?string $partner): self
    {
        $this->partner = $partner;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;
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

    public function getActivateUrl(): string
    {
        return $this->activateUrl;
    }

    public function setActivateUrl(string $activateUrl): self
    {
        $this->activateUrl = $activateUrl;
        return $this;
    }

    public function getCallbackUrl(): string
    {
        return $this->callbackUrl;
    }

    public function setCallbackUrl(string $callbackUrl): self
    {
        $this->callbackUrl = $callbackUrl;
        return $this;
    }

}
