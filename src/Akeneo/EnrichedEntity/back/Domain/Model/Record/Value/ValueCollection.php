<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Domain\Model\Record\Value;

use Webmozart\Assert\Assert;

class ValueCollection
{
    /** @var array */
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

    public function normalize(): array
    {
        $valuesNormalized = [];
        foreach ($this->values as $value) {
            $valuesNormalized[] = $value->normalize();
        }

        return $valuesNormalized;
    }

    public function setValue(Value $newValue): ValueCollection
    {
        $values = array_filter($this->values, function (Value $value) use ($newValue) {
            return !($newValue->sameChannel($value) && $newValue->sameLocale($value) && $newValue->sameAttribute($value));
        });

        $values[] = $newValue;

        return new self($values);
    }

    public static function fromValues(array $values): ValueCollection
    {
        return new self($values);
    }
}
