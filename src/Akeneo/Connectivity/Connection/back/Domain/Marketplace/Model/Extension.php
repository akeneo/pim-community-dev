<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Marketplace\Model;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Extension
{
    private string $id;
    private string $name;
    private string $logo;
    private string $author;
    private ?string $partner = null;
    private ?string $description = null;
    private string $url;
    private bool $certified;
    /** @var array<string> */
    private array $categories;

    private const REQUIRED_KEYS = [
        'id',
        'name',
        'logo',
        'author',
        'url',
        'categories',
    ];

    private function __construct()
    {
    }

    /**
     * @param array{
     *     id?: string,
     *     name?: string,
     *     logo?: string,
     *     author: string,
     *     partner?: string,
     *     description?: string,
     *     url?: string,
     *     categories?: array<string>,
     *     certified?: bool,
     * } $values
     */
    public static function fromWebMarketplaceValues(array $values): self
    {
        foreach (self::REQUIRED_KEYS as $key) {
            if (!isset($values[$key])) {
                throw new \InvalidArgumentException(\sprintf('Missing property "%s" in given extension', $key));
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
         * } $values
         */

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

        return $self;
    }

    /**
     * @param array<string> $queryParameters
     */
    public function withAnalytics(array $queryParameters): self
    {
        $query = \http_build_query($queryParameters);

        $values = $this->normalize();

        $url = $values['url'];
        $url = \parse_url($url, PHP_URL_QUERY) ? \sprintf('%s&%s', $url, $query) : \sprintf('%s?%s', $url, $query);

        $values['url'] = $url;

        /* @phpstan-ignore-next-line */
        return self::fromWebMarketplaceValues($values);
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
        ];
    }
}
