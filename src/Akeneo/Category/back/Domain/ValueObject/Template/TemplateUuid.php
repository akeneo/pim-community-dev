<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\ValueObject\Template;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class TemplateUuid
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
            throw new \InvalidArgumentException(sprintf('Template uuid should be a valid uuid, %s given', $uuid));
        }

        return new self(Uuid::fromString($uuid));
    }

    public function __toString(): string
    {
        return $this->uuid->toString();
    }

    public function getValue(): string
    {
        return $this->__toString();
    }

    public function toBytes(): string
    {
        return $this->uuid->getBytes();
    }
}
