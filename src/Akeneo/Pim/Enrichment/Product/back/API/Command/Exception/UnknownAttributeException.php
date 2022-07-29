<?php

namespace Akeneo\Pim\Enrichment\Product\API\Command\Exception;

class UnknownAttributeException extends \InvalidArgumentException
{
    public function __construct(
        private string $attributeCode,
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct(
            \sprintf('Could not find the %s attribute', $attributeCode),
            $code,
            $previous
        );
    }

    public function getAttributeCode(): string
    {
        return $this->attributeCode;
    }
}
