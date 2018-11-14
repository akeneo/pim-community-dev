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

namespace Akeneo\Pim\Automation\SuggestData\Domain\Model;

/**
 * It structures data that comes from Franklin and that allows to create proposals.
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
final class SuggestedData implements \IteratorAggregate, \JsonSerializable
{
    /** @var SuggestedValue[] */
    private $values = [];

    /**
     * @param array|null $values
     */
    public function __construct(?array $values)
    {
        if (null !== $values) {
            foreach ($values as $value) {
                $this->values[] = new SuggestedValue($value['name'], $value['value']);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        if ($this->isEmpty()) {
            return null;
        }

        return array_map(
            function (SuggestedValue $suggestedValue) {
                return [
                    'name' => $suggestedValue->name(),
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
