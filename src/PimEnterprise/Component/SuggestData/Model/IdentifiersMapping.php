<?php

declare(strict_types=1);

namespace PimEnterprise\Component\SuggestData\Model;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

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
    public function getIdentifier(string $name): ?AttributeInterface
    {
        if (array_key_exists($name, $this->identifiers)) {
            return $this->identifiers[$name];
        }

        return null;
    }

    public function normalize(): array
    {
        $result = [];
        foreach ($this->identifiers as $pimAiCode => $attribute) {
            $result[$pimAiCode] = $attribute->getCode();
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
}
