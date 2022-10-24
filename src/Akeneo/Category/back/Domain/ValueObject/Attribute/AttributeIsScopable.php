<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\ValueObject\Attribute;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeIsScopable
{
    private function __construct(
        private bool $isScopable,
    ) {
    }

    public static function fromBoolean(bool $isScopable): self
    {
        return new self($isScopable);
    }

    public function getValue(): bool
    {
        return $this->isScopable;
    }

    public function normalize(): bool
    {
        return $this->isScopable;
    }
}
