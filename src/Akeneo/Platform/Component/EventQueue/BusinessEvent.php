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
    private $author;
    private $data;
    private $timestamp;
    private $uuid;

    public function __construct(
        string $author,
        array $data,
        int $timestamp = null,
        string $uuid = null
    ) {
        $this->author = $author;
        $this->data = $data;
        $this->timestamp = $timestamp ?? time();
        $this->uuid = $uuid ?? Uuid::uuid4()->toString();
    }

    abstract public function name(): string;

    public function author(): string
    {
        return $this->author;
    }

    public function data(): array
    {
        return $this->data;
    }

    public function timestamp(): int
    {
        return $this->timestamp;
    }

    public function uuid(): string
    {
        return $this->uuid;
    }
}
