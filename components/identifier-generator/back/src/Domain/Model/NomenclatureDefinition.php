<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model;

use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-type NormalizedNomenclatureDefinition array{operator: string, value: int, generate_if_empty: bool}
 */
class NomenclatureDefinition
{
    public function __construct(
        private readonly ?string $operator = null,
        private readonly ?int $value = null,
        private readonly ?bool $generateIfEmpty = null,
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

    public function withOperator(string $operator): self
    {
        return new NomenclatureDefinition($operator, $this->value, $this->generateIfEmpty);
    }

    public function withValue(int $value): self
    {
        return new NomenclatureDefinition($this->operator, $value, $this->generateIfEmpty);
    }

    public function withGenerateIfEmpty(bool $generateIfEmpty): self
    {
        return new NomenclatureDefinition($this->operator, $this->value, $generateIfEmpty);
    }

    /**
     * @return NormalizedNomenclatureDefinition
     */
    public function normalize(): array
    {
        Assert::notNull($this->operator);
        Assert::notNull($this->value);
        Assert::notNull($this->generateIfEmpty);

        return [
            'operator' => $this->operator,
            'value' => $this->value,
            'generate_if_empty' => $this->generateIfEmpty,
        ];
    }
}
