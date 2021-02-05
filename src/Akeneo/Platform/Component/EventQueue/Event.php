<?php

declare(strict_types=1);

namespace Akeneo\Platform\Component\EventQueue;

use Ramsey\Uuid\Uuid;

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

    /**
     * @return array{
     *  type: string,
     *  uuid: string,
     *  author: string,
     *  author_type: string,
     *  timestamp: int,
     * }
     */
    public function toLog(): array
    {
        return [
            'type' => $this->getName(),
            'uuid' => $this->uuid,
            'author' => $this->author->name(),
            'author_type' => $this->author->type(),
            'timestamp' => $this->timestamp,
        ];
    }
}
