<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Marketplace\DTO;

use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Extension
{
    private Uuid $id;
    private string $name;
    private string $logo;
    private string $author;
    private string $partner;
    private string $description;
    private string $url;
    private bool $certified;

    /** @var array <string> */
    private array $categories;

    private const REQUIRED_KEYS = [
        'id',
        'name',
        'logo',
        'author',
        'partner',
        'description',
        'url',
        'categories',
        'certified',
    ];

    private function __construct(
        Uuid $id,
        string $name,
        string $logo,
        string $author,
        string $partner,
        string $description,
        string $url,
        array $categories,
        bool $certified = false
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->logo = $logo;
        $this->author = $author;
        $this->partner = $partner;
        $this->description = $description;
        $this->url = $url;
        $this->categories = $categories;
        $this->certified = $certified;
    }

    public static function create(array $values): self
    {
        foreach (self::REQUIRED_KEYS as $key) {
            Assert::keyExists($values, $key);
        }

        return new self(
            Uuid::fromString($values['id']),
            $values['name'],
            $values['logo'],
            $values['author'],
            $values['partner'],
            $values['description'],
            $values['url'],
            $values['categories'],
            $values['certified']
        );
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function logo(): string
    {
        return $this->logo;
    }

    public function author(): string
    {
        return $this->author;
    }

    public function partner(): string
    {
        return $this->partner;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function certified(): bool
    {
        return $this->certified;
    }

    public function categories(): array
    {
        return $this->categories;
    }

    /**
     * @return array{
     *  id: string,
     *  name: string,
     *  logo: string,
     *  author: string,
     *  partner: string,
     *  description: string,
     *  url: string,
     *  categories: array<string>,
     *  certified: bool,
     * }
     */
    public function normalize(): array
    {
        return [
            'id' => $this->id->toString(),
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
