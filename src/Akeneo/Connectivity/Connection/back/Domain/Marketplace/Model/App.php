<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Marketplace\Model;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class App
{
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

    private const CUSTOM_APP_REQUIRED_KEYS = [
        'id',
        'name',
        'activate_url',
        'callback_url',
    ];

    /**
     * @param array<string> $categories
     */
    private function __construct(
        private readonly string $id,
        private readonly string $name,
        private readonly ?string $logo,
        private readonly ?string $author,
        private readonly ?string $partner,
        private readonly ?string $description,
        private readonly ?string $url,
        private readonly bool $certified,
        private readonly array $categories,
        private string $activateUrl,
        private readonly string $callbackUrl,
        private readonly bool $connected,
        private readonly bool $isPending,
        private readonly bool $isCustomApp,
    ) {
        if ($this->isPending && $this->connected) {
            throw new \DomainException('An App can not be both connected and pending.');
        }
    }

    /**
     * @param array{
     *     id?: string,
     *     name?: string,
     *     logo?: string,
     *     author?: string,
     *     partner?: string,
     *     description?: string,
     *     url?: string,
     *     categories?: array<string>,
     *     certified?: bool,
     *     activate_url?: string,
     *     callback_url?: string,
     *     connected?: bool,
     *     isPending?: bool,
     * } $values
     */
    public static function fromWebMarketplaceValues(array $values): self
    {
        foreach (self::MARKETPLACE_REQUIRED_KEYS as $key) {
            if (!isset($values[$key])) {
                throw new \InvalidArgumentException(\sprintf('Missing property "%s" in given app', $key));
            }
        }

        /** @phpstan-var array{
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
         *     isPending?: bool,
         * } $values
         */

        return new self(
            $values['id'],
            $values['name'],
            $values['logo'],
            $values['author'],
            $values['partner'] ?? null,
            $values['description'] ?? null,
            $values['url'],
            $values['certified'] ?? false,
            $values['categories'],
            $values['activate_url'],
            $values['callback_url'],
            $values['connected'] ?? false,
            $values['isPending'] ?? false,
            false,
        );
    }

    /**
     * @param array{
     *     id?: string,
     *     name?: string,
     *     author?: string,
     *     activate_url?: string,
     *     callback_url?: string,
     *     connected?: bool,
     *     isPending?: bool,
     * } $values
     */
    public static function fromCustomAppValues(array $values): self
    {
        foreach (self::CUSTOM_APP_REQUIRED_KEYS as $key) {
            if (!isset($values[$key])) {
                throw new \InvalidArgumentException(\sprintf('Missing property "%s" in given app', $key));
            }
        }

        /** @phpstan-var array{
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

        /** @phpstan-var array{
         *     id: string,
         *     name: string,
         *     author?: string,
         *     activate_url: string,
         *     callback_url: string,
         *     connected?: bool,
         *     isPending?: bool,
         * } $values
         */

        return new self(
            $values['id'],
            $values['name'],
            null,
            $values['author'] ?? null,
            null,
            null,
            null,
            false,
            [],
            $values['activate_url'],
            $values['callback_url'],
            $values['connected'] ?? false,
            $values['isPending'] ?? false,
            true,
        );
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

        $values['url'] = self::appendQueryParametersToUrl($values['url'], $queryParameters);

        /* @phpstan-ignore-next-line */
        return self::fromWebMarketplaceValues($values);
    }

    /**
     * @param array<string> $queryParameters
     */
    public function withPimUrlSource(array $queryParameters): self
    {
        $app = clone $this;
        $app->activateUrl = self::appendQueryParametersToUrl(
            $app->activateUrl,
            $queryParameters
        );

        return $app;
    }

    public function withConnectedStatus(bool $isConnected): self
    {
        return new self(
            $this->id,
            $this->name,
            $this->logo,
            $this->author,
            $this->partner,
            $this->description,
            $this->url,
            $this->certified,
            $this->categories,
            $this->activateUrl,
            $this->callbackUrl,
            $isConnected,
            $this->isPending,
            $this->isCustomApp,
        );
    }

    /**
     * @param array<string> $queryParameters
     */
    private static function appendQueryParametersToUrl(string $url, array $queryParameters): string
    {
        $query = \http_build_query($queryParameters);

        return \parse_url($url, PHP_URL_QUERY) ? \sprintf('%s&%s', $url, $query) : \sprintf('%s?%s', $url, $query);
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
     *  isCustomApp: bool,
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
            'isPending' => $this->isPending,
            'isCustomApp' => $this->isCustomApp,
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

    public function isCustomApp(): bool
    {
        return $this->isCustomApp;
    }

    public function isPending(): bool
    {
        return $this->isPending;
    }

    public function withIsPending(): self
    {
        return new self(
            $this->id,
            $this->name,
            $this->logo,
            $this->author,
            $this->partner,
            $this->description,
            $this->url,
            $this->certified,
            $this->categories,
            $this->activateUrl,
            $this->callbackUrl,
            false,
            true,
            $this->isCustomApp,
        );
    }
}
