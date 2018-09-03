<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Domain\Model\Record\Value;

use Webmozart\Assert\Assert;

class ValueCollection implements \IteratorAggregate
{
    /** @var Value[] */
    private $values;

    private function __construct(array $values)
    {
        Assert::allIsInstanceOf(
            $values,
            Value::class,
            sprintf('All values should be instance of %s', Value::class)
        );

        $this->values = $values;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->values);
    }

    public function normalize(): array
    {
        $valuesNormalized = [];
        foreach ($this->values as $value) {
            $valuesNormalized[] = $value->normalize();
        }

        return $valuesNormalized;
    }

    public function add(Value $value): ValueCollection
    {
        $values = $this->values;
        $values[] = $value;

        return new self($values);
    }

    public static function fromValues(array $values): ValueCollection
    {
        return new self($values);
    }
}
