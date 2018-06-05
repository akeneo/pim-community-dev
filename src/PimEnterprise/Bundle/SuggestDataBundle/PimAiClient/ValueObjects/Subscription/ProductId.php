<?php

declare(strict_types=1);

namespace PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\ValueObjects\Subscription;

class ProductId
{
    private
        $identifierName,
        $value;

    public function __construct(string $identifierName, string $value)
    {
        $identifierName = trim($identifierName);
        $value = trim($value);

        $this->validate($identifierName, $value);

        $this->identifierName = $identifierName;
        $this->value = $value;
    }

    public function identifierName(): string
    {
        return $this->identifierName;
    }

    public function value(): string
    {
        return $this->value;
    }

    private function validate(string $identifierName, string $value)
    {
        if(empty($identifierName))
        {
            throw new \InvalidArgumentException('Product identifier name name must not be empty');
        }

        if(empty($value))
        {
            throw new \InvalidArgumentException('Product identifier value must not be empty');
        }
    }
}
