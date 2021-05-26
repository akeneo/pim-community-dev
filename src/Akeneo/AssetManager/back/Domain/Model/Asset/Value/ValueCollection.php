<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Domain\Model\Asset\Value;

use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;
use Webmozart\Assert\Assert;

/**
 * Values are indexed by value keys.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 */
class ValueCollection implements \IteratorAggregate, \Countable
{
    private array $values;

    private function __construct(array $values)
    {
        $this->values = $values;
    }

    public function normalize(): array
    {
        return array_map(fn (Value $value) => $value->normalize(), $this->values);
    }

    public function findValue(ValueKey $valueKey): ?Value
    {
        $key = (string) $valueKey;

        return $this->values[$key] ?? null;
    }

    public function hasValue(Value $value): bool
    {
        $existingValue = $this->findValue($value->getValueKey());
        if (! $existingValue instanceof Value) {
            return $value->isEmpty();
        }

        return $value->equals($existingValue);
    }

    public function setValue(Value $newValue): ValueCollection
    {
        $values = $this->values;
        $key = $newValue->getValueKey()->__toString();

        if ($newValue->isEmpty()) {
            unset($values[$key]);
        } else {
            $values[$key] = $newValue;
        }

        return new self($values);
    }

    public static function fromValues(array $values): ValueCollection
    {
        Assert::allIsInstanceOf(
            $values,
            Value::class,
            sprintf('All values should be instance of %s', Value::class)
        );
        $indexedValues = [];
        foreach ($values as $value) {
            $indexedValues[(string) $value->getValueKey()] = $value;
        }

        return new self($indexedValues);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->values);
    }

    public function filter(\Closure $closure): self
    {
        return new self(array_filter($this->values, $closure));
    }

    public function count(): int
    {
        return count($this->values);
    }
}
