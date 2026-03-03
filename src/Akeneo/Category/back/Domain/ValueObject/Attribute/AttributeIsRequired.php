<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\ValueObject\Attribute;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeIsRequired
{
    private function __construct(
        private bool $isRequired,
    ) {
    }

    public static function fromBoolean(bool $isRequired): self
    {
        return new self($isRequired);
    }

    public function getValue(): bool
    {
        return $this->isRequired;
    }

    public function normalize(): bool
    {
        return $this->isRequired;
    }
}
