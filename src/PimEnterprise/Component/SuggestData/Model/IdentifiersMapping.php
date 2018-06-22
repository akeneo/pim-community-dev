<?php

declare(strict_types=1);

namespace PimEnterprise\Component\SuggestData\Model;

class IdentifiersMapping implements \IteratorAggregate
{
    private $identifiers;

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
     * @return null|string
     */
    public function getIdentifier(string $name): ?string
    {
        if (array_key_exists($name, $this->identifiers)) {
            return $this->identifiers[$name];
        }

        return null;
    }

    /**
     * @return \ArrayIterator|\Traversable
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->identifiers);
    }
}
