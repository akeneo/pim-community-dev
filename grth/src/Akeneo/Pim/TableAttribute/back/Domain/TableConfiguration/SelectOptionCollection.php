<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Domain\TableConfiguration;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\SelectOptionCode;

/**
 * @phpstan-implements \IteratorAggregate<string, SelectOption>
 */
final class SelectOptionCollection implements \IteratorAggregate
{
    public const MAX_OPTIONS = 20000;

    /** @var array<string, SelectOption> */
    private array $options;

    /**
     * @param SelectOption[] $options
     */
    private function __construct(array $options)
    {
        $this->options = [];
        foreach ($options as $option) {
            $this->options[\strtolower($option->code()->asString())] = $option;
        }
    }

    /**
     * @param array<int, mixed> $options
     */
    public static function fromNormalized(array $options): self
    {
        return new self(\array_map(
            fn (array $normalizedOption): SelectOption => SelectOption::fromNormalized($normalizedOption),
            $options
        ));
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return array<int, mixed>
     */
    public function normalize(): array
    {
        return array_map(
            fn (SelectOption $option): array => $option->normalize(),
            \array_values($this->options),
        );
    }

    /**
     * @return SelectOptionCode[]
     */
    public function getOptionCodes(): array
    {
        return \array_values(\array_map(
            fn (SelectOption $selectOption): SelectOptionCode => $selectOption->code(),
            $this->options
        ));
    }

    public function getByCode(string $optionCode): ?SelectOption
    {
        return $this->options[\strtolower($optionCode)] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->options);
    }
}
