<?php

declare(strict_types=1);

namespace PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\ValueObjects\Subscription;

class Request
{
    private
        $productCodes;

    public function __construct()
    {
        $this->productCodes = [];
    }

    public function addProductCode(ProductCode $productCode): self
    {
        $this->productCodes[] = $productCode;

        return $this;
    }

    public function toArray()
    {
        $result = [];
        foreach($this->productCodes as $productCode)
        {
            $result[$productCode->identifierName()][] = $productCode->value();
        }

        return $result;
    }
}
