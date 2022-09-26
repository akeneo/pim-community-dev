<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model;

/**
 * String between each Properties chosen to create the Structure of the identifier
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Delimiter
{
    private function __construct(
        private string $value,
    ) {
    }

    public static function fromString(string $delimiter): self
    {
        return new self($delimiter);
    }

    public function asString(): string
    {
        return $this->value;
    }
}
