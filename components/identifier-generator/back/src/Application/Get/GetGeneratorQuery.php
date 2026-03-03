<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Get;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetGeneratorQuery
{
    private function __construct(
        private readonly string $identifierGeneratorCode
    ) {
    }

    public static function fromCode(string $identifierGeneratorCode): self
    {
        return new self($identifierGeneratorCode);
    }

    public function getIdentifierGeneratorCode(): string
    {
        return $this->identifierGeneratorCode;
    }
}
