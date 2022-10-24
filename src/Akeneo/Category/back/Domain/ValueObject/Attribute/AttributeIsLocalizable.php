<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\ValueObject\Attribute;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeIsLocalizable
{
    private function __construct(
        private bool $isLocalizable,
    ) {
    }

    public static function fromBoolean(bool $isLocalizable): self
    {
        return new self($isLocalizable);
    }

    public function getValue(): bool
    {
        return $this->isLocalizable;
    }

    public function normalize(): bool
    {
        return $this->isLocalizable;
    }
}
