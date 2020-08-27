<?php

declare(strict_types=1);

namespace Akeneo\Platform\Component\EventQueue;

use Ramsey\Uuid\Uuid;

/**
 * @copyright 202O Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class BusinessEvent implements BusinessEventInterface
{
    private $name;
    private $author;
    private $data;
    private $timestamp;
    private $uuid;

    public function __construct(
        string $name,
        string $author,
        array $data,
        ?int $timestamp,
        ?string $uuid
    ) {
        $this->name = $name;
        $this->author = $author;
        $this->data = $data;
        $this->timestamp = $timestamp ?? time();
        $this->uuid = $uuid ?? Uuid::uuid4()->toString();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAuthor(): string
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
}
