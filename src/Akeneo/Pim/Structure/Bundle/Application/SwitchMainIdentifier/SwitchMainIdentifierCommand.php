<?php

namespace Akeneo\Pim\Structure\Bundle\Application\SwitchMainIdentifier;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SwitchMainIdentifierCommand
{
    public function __construct(
        private readonly string $newMainIdentifierCode,
    ) {
    }

    public static function fromIdentifierCode(string $newMainIdentifierCode)
    {
        return new self($newMainIdentifierCode);
    }

    public function getNewMainIdentifierCode(): string
    {
        return $this->newMainIdentifierCode;
    }
}
