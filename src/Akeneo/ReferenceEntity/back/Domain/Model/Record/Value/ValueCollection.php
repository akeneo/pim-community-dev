<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Domain\Model\Record\Value;

use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKey;
use Webmozart\Assert\Assert;

/**
 * Values are indexed by value keys.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 */
class ValueCollection implements \IteratorAggregate
{
    private function __construct(
        private array $values
    ) {
    }

    public function normalize(): array
    {
        return array_map(static fn (Value $value) => $value->normalize(), $this->values);
    }

    public function findValue(ValueKey $valueKey): ?Value
    {
        $key = (string) $valueKey;

        return $this->values[$key] ?? null;
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
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->values);
    }

    public function filter(\Closure $closure): self
    {
        return new self(array_filter($this->values, $closure));
    }
}
