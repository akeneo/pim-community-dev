<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject;

final class ProductCodeCollection implements \IteratorAggregate
{
    private $productCodes;

    public function __construct()
    {
        $this->productCodes = [];
    }

    public function add(ProductCode $productCode): self
    {
        $this->productCodes[] = $productCode;

        return $this;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->productCodes);
    }

    public function toArray()
    {
        $result = [];
        foreach ($this->productCodes as $productCode) {
            $result[$productCode->identifierName()][] = $productCode->value();
        }
        return $result;
    }
}
