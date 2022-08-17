<?php

declare(strict_types=1);

namespace Akeneo\Pim\TableAttribute\Domain\TableConfiguration;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Event\Event;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Event\SelectOptionWasDeleted;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\SelectOptionCode;

class WriteSelectOptionCollection
{
    /** @var array<string|int, SelectOption> */
    private array $options;

    /** @var array<int, Event> */
    private array $events;

    private function __construct(SelectOptionCollection $collection)
    {
        $this->options = [];
        $this->events = [];
        /** @var SelectOption $selectOption */
        foreach ($collection as $selectOption) {
            $this->options[$selectOption->code()->asString()] = $selectOption;
        }
    }

    public static function fromReadSelectOptionCollection(SelectOptionCollection $collection): self
    {
        return new WriteSelectOptionCollection($collection);
    }

    /**
     * @param array<int, array<string, mixed>> $normalizedCollection
     */
    public function update(string $attributeCode, ColumnCode $columnCode, array $normalizedCollection): void
    {
        foreach ($normalizedCollection as $normalizedOption) {
            $code = $normalizedOption['code'];
            $this->options[$code] = SelectOption::fromNormalized($normalizedOption);
        }

        $indexedUpdatedCodes = \array_flip(\array_map(
            fn (array $option): string => $option['code'],
            $normalizedCollection
        ));
        foreach ($this->options as $code => $option) {
            if (!\array_key_exists($code, $indexedUpdatedCodes)) {
                unset($this->options[$code]);
                $this->events[] = new SelectOptionWasDeleted(
                    $attributeCode,
                    $columnCode,
                    SelectOptionCode::fromString((string) $code)
                );
            }
        }
    }

    /**
     * @return Event[]
     */
    public function releaseEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
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
}
