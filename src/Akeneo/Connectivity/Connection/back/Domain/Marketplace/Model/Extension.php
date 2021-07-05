<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Marketplace\Model;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Extension
{
    private UuidInterface $id;
    private string $name;
    private string $logo;
    private string $author;
    private string $partner;
    private string $description;
    private string $url;
    private bool $certified;

    /** @var array<string> */
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

    private function __construct()
    {
    }

    /**
     * @param array{
     *     id: string,
     *     name: string,
     *     logo: string,
     *     author: string,
     *     partner: string,
     *     description: string,
     *     url: string,
     *     categories: array<string>,
     *     certified: bool,
     * } $values
     */
    public static function create(array $values): self
    {
        foreach (self::REQUIRED_KEYS as $key) {
            Assert::keyExists($values, $key);
        }

        $self = new self();

        $self->id = Uuid::fromString($values['id']);
        $self->name = $values['name'];
        $self->logo = $values['logo'];
        $self->author = $values['author'];
        $self->partner = $values['partner'];
        $self->description = $values['description'];
        $self->url = $values['url'];
        $self->categories = $values['categories'];
        $self->certified = $values['certified'];

        return $self;
    }

    public function id(): UuidInterface
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

    /**
     * @return  array<string>
     */
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
