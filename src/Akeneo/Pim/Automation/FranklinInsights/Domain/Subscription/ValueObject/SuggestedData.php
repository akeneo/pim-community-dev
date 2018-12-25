<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject;

/**
 * It structures data coming from the data provider and allows to create proposals.
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
final class SuggestedData implements \IteratorAggregate
{
    /** @var SuggestedValue[] */
    private $values = [];

    /**
     * @param array $values
     */
    public function __construct(array $values)
    {
        foreach ($values as $value) {
            $this->values[] = new SuggestedValue($value['pimAttributeCode'], $value['value']);
        }
    }

    /**
     * @return array|null
     */
    public function getRawValues(): ?array
    {
        if ($this->isEmpty()) {
            return null;
        }

        return array_map(
            function (SuggestedValue $suggestedValue) {
                return [
                    'pimAttributeCode' => $suggestedValue->pimAttributeCode(),
                    'value' => $suggestedValue->value(),
                ];
            },
            $this->values
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->values);
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->values);
    }
}
