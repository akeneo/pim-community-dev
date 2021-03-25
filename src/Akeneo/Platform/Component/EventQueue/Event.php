<?php

declare(strict_types=1);

namespace Akeneo\Platform\Component\EventQueue;

use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

/**
 * @copyright 202O Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class Event implements EventInterface
{
    private Author $author;
    protected array $data;
    private int $timestamp;
    private string $uuid;

    public function __construct(
        Author $author,
        array $data,
        int $timestamp = null,
        string $uuid = null
    ) {
        Assert::keyExists($data, 'origin');

        $this->author = $author;
        $this->data = $data;
        $this->timestamp = $timestamp ?? time();
        $this->uuid = $uuid ?? Uuid::uuid4()->toString();
    }

    abstract public function getName(): string;

    public function getAuthor(): Author
    {
        return $this->author;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getOrigin(): string
    {
        return $this->data['origin'];
    }
}
