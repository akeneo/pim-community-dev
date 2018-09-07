<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Domain\Model\Record\Value;

use Webmozart\Assert\Assert;

/**
 * Values are indexed by value keys.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 */
class ValueCollection
{
    /** @var array */
    private $values;

    private function __construct(array $values)
    {
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
        $key = $newValue->getValueKey()->__toString();
        $values[$key] = $newValue;

        return new self($values);
    }

    // TODO SPEC IT
    /**
     * @param Value $values
     */
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
}
