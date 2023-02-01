<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-type NormalizedNomenclatureDefinition array{operator: string, value: int, generate_if_empty: bool}
 */
final class NomenclatureDefinition
{
    /**
     * @param array<string, ?string>|null $values
     */
    public function __construct(
        private readonly ?string $operator = null,
        private readonly ?int $value = null,
        private readonly ?bool $generateIfEmpty = null,
        private readonly ?array $values = [],
    ) {
    }

    public function generateIfEmpty(): ?bool
    {
        return $this->generateIfEmpty;
    }

    public function operator(): ?string
    {
        return $this->operator;
    }

    public function value(): ?int
    {
        return $this->value;
    }

    /**
     * @return array<string, ?string>
     */
    public function values(): array
    {
        return $this->values ?? [];
    }

    public function withOperator(string $operator): self
    {
        return new NomenclatureDefinition($operator, $this->value, $this->generateIfEmpty, $this->values);
    }

    public function withValue(int $value): self
    {
        return new NomenclatureDefinition($this->operator, $value, $this->generateIfEmpty, $this->values);
    }

    public function withGenerateIfEmpty(bool $generateIfEmpty): self
    {
        return new NomenclatureDefinition($this->operator, $this->value, $generateIfEmpty, $this->values);
    }

    /**
     * @param array<string, ?string> $values
     */
    public function withValues(array $values): self
    {
        return new NomenclatureDefinition($this->operator, $this->value, $this->generateIfEmpty, $values);
    }
}
