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
    private ?string $logo;
    private ?string $author;
    private ?string $partner;
    private ?string $description;
    private ?string $url;
    private bool $certified;
    /** @var array<string> */
    private array $categories;
    private string $activateUrl;
    private string $callbackUrl;
    private bool $connected;
    private bool $isTestApp;

    private const MARKETPLACE_REQUIRED_KEYS = [
        'id',
        'name',
        'logo',
        'author',
        'url',
        'categories',
        'activate_url',
        'callback_url',
    ];

    private const TEST_APP_REQUIRED_KEYS = [
        'id',
        'name',
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
     *     connected?: bool,
     * } $values
     */
    public static function fromWebMarketplaceValues(array $values): self
    {
        foreach (self::MARKETPLACE_REQUIRED_KEYS as $key) {
            if (!isset($values[$key])) {
                throw new \InvalidArgumentException(\sprintf('Missing property "%s" in given app', $key));
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
        $self->connected = $values['connected'] ?? false;
        $self->isTestApp = false;

        return $self;
    }

    /**
     * @param array{
     *     id: string,
     *     name: string,
     *     author?: string,
     *     activate_url: string,
     *     callback_url: string,
     *     connected?: bool,
     * } $values
     */
    public static function fromTestAppValues(array $values): self
    {
        foreach (self::TEST_APP_REQUIRED_KEYS as $key) {
            if (!isset($values[$key])) {
                throw new \InvalidArgumentException(\sprintf('Missing property "%s" in given app', $key));
            }
        }

        $self = new self();

        $self->id = $values['id'];
        $self->name = $values['name'];
        $self->logo = null;
        $self->author = $values['author'] ?? null;
        $self->partner = null;
        $self->description = null;
        $self->url = null;
        $self->categories = [];
        $self->certified = false;
        $self->activateUrl = $values['activate_url'];
        $self->callbackUrl = $values['callback_url'];
        $self->connected = $values['connected'] ?? false;
        $self->isTestApp = true;

        return $self;
    }

    /**
     * @param array<string> $queryParameters
     */
    public function withAnalytics(array $queryParameters): self
    {
        $values = $this->normalize();

        if (null === $values['url']) {
            return $this;
        }

        $values['url'] = static::appendQueryParametersToUrl($values['url'], $queryParameters);

        /* @phpstan-ignore-next-line */
        return self::fromWebMarketplaceValues($values);
    }

    /**
     * @param array<string> $queryParameters
     */
    public function withPimUrlSource(array $queryParameters): self
    {
        $app = clone $this;
        $app->activateUrl = static::appendQueryParametersToUrl(
            $app->activateUrl,
            $queryParameters
        );

        return  $app;
    }

    /**
     * @param bool $isConnected
     * @return App
     */
    public function withConnectedStatus(bool $isConnected): self
    {
        $this->connected = $isConnected;
        return $this;
    }

    /**
     * @param array<string> $queryParameters
     */
    private static function appendQueryParametersToUrl(string $url, array $queryParameters): string
    {
        $query = \http_build_query($queryParameters);

        if (\parse_url($url, PHP_URL_QUERY)) {
            $url = \sprintf('%s&%s', $url, $query);
        } else {
            $url = \sprintf('%s?%s', $url, $query);
        }

        return $url;
    }

    /**
     * @return array{
     *  id: string,
     *  name: string,
     *  logo: string|null,
     *  author: string|null,
     *  partner: string|null,
     *  description: string|null,
     *  url: string|null,
     *  categories: array<string>,
     *  certified: bool,
     *  activate_url: string,
     *  callback_url: string,
     *  connected: bool,
     *  isTestApp: bool,
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
            'connected' => $this->connected,
            'isTestApp' => $this->isTestApp,
        ];
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getActivateUrl(): string
    {
        return $this->activateUrl;
    }

    public function getCallbackUrl(): string
    {
        return $this->callbackUrl;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function getPartner(): ?string
    {
        return $this->partner;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function isCertified(): bool
    {
        return $this->certified;
    }

    /**
     * @return string[]
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    public function isTestApp(): bool
    {
        return $this->isTestApp;
    }
}
