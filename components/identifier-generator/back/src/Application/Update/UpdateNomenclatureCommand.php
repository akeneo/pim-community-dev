<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Update;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\CommandInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateNomenclatureCommand implements CommandInterface
{
    /**
     * @param array<string, ?string> $values
     */
    public function __construct(
        private readonly string $propertyCode,
        private readonly ?string $operator,
        private readonly ?int $value,
        private readonly ?bool $generateIfEmpty,
        private readonly ?array $values = [],
    ) {
    }

    /**
     * @return array<string, ?string>
     */
    public function getValues(): array
    {
        return $this->values ?? [];
    }

    public function getOperator(): ?string
    {
        return $this->operator;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function getGenerateIfEmpty(): ?bool
    {
        return $this->generateIfEmpty;
    }

    public function getPropertyCode(): string
    {
        return $this->propertyCode;
    }
}
