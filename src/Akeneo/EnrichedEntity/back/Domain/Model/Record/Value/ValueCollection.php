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
        return array_map(function (Value $value) {
            return $value->normalize();
        }, $this->values);
    }

    public function setValue(Value $newValue): ValueCollection
    {
        $values = $this->values;
        $key = ValueCollection::getKey($newValue);
        $values[$key] = $newValue;

        return new self($values);
    }

    public static function fromValues(array $values): ValueCollection
    {
        return new self($values);
    }

    private static function getKey(Value $value): string
    {
        return sprintf(
            '%s%s%s',
            $value->getAttributeIdentifier()->normalize(),
            $value->hasChannel() ? $value->getChannelReference()->getIdentifier()->normalize() : '',
            $value->hasLocale() ? $value->getLocaleReference()->getIdentifier()->normalize() : ''
        );
    }
}
