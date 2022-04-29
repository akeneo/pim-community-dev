<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductUuid implements ProductEntityIdInterface
{
    private function __construct(private UuidInterface $uuid)
    {
    }

    public static function fromUuid(UuidInterface $uuid): self
    {
        return new self($uuid);
    }

    public static function fromString(string $uuid): self
    {
        if (!Uuid::isValid($uuid)) {
            throw new \InvalidArgumentException(sprintf('Product uuid should be a valid uuid, %s given', $uuid));
        }

        return new self(Uuid::fromString($uuid));
    }

    public function __toString(): string
    {
        return $this->uuid->toString();
    }

    public function toBytes(): string
    {
        return $this->uuid->getBytes();
    }
}
