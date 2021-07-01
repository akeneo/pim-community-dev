<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Domain\TableConfiguration;

use Webmozart\Assert\Assert;

final class SelectOptionCollection
{
    /** @var array<string, SelectOption> */
    private array $options;

    /**
     * @param SelectOption[] $options
     */
    private function __construct(array $options)
    {
        $this->options = [];
        foreach ($options as $option) {
            $this->options[$option->code()] = $option;
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
     * @return string[]
     */
    public function getOptionCodes(): array
    {
        return \array_values(\array_map(
            fn (SelectOption $selectOption): string => $selectOption->code(),
            $this->options
        ));
    }

    public function hasOptionCode(string $optionCode): bool
    {
        return array_key_exists($optionCode, $this->options);
    }
}
