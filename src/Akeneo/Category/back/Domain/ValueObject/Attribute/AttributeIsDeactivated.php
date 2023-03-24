<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\ValueObject\Attribute;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeIsDeactivated
{
    private function __construct(
        private bool $isDeactivated,
    ) {
    }

    public static function fromBoolean(bool $isDeactivated): self
    {
        return new self($isDeactivated);
    }

    public function getValue(): bool
    {
        return $this->isDeactivated;
    }

    public function normalize(): bool
    {
        return $this->isDeactivated;
    }
}
