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

namespace Akeneo\Pim\Automation\SuggestData\Component\Model;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * Holds the identifiers mapping. Collection of IdentifierMapping entities
 */
class IdentifiersMapping implements \IteratorAggregate
{
    public const PIM_AI_IDENTIFIERS = [
        'brand',
        'mpn',
        'upc',
        'asin',
    ];

    private $identifiers;

    /**
     * @param array $identifiers
     */
    public function __construct(array $identifiers)
    {
        $this->identifiers = $identifiers;
    }

    /**
     * @return array
     */
    public function getIdentifiers(): array
    {
        return $this->identifiers;
    }

    /**
     * @param string $name
     *
     * @return null|AttributeInterface
     */
    public function getIdentifier(string $name): ?AttributeInterface
    {
        if (array_key_exists($name, $this->identifiers)) {
            return $this->identifiers[$name];
        }

        return null;
    }

    /**
     * @return array
     */
    public function normalize(): array
    {
        $result = [];
        foreach (static::PIM_AI_IDENTIFIERS as $identifier) {
            $value = null;
            if (array_key_exists($identifier, $this->identifiers)) {
                $value = $this->identifiers[$identifier] instanceof AttributeInterface
                    ? $this->identifiers[$identifier]->getCode()
                    : null;
            }

            $result[$identifier] = $value;
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getIterator(): iterable
    {
        return new \ArrayIterator($this->identifiers);
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->identifiers);
    }
}
