<?php

namespace Akeneo\Pim\Enrichment\Product\API\Command\Exception;

class UnknownUserIntentException extends \InvalidArgumentException
{
    public function __construct(
        private string $fieldName,
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct(
            \sprintf('Cannot create userIntent from %s fieldName', $fieldName),
            $code,
            $previous
        );
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }
}
