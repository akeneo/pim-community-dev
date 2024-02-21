<?php

namespace Akeneo\Pim\Structure\Component\Exception;

class CannotRemoveAttributeException extends \RuntimeException
{
    public function __construct(
        public readonly string $messageTemplate,
        public readonly array $messageParameters = [],
    ) {
        parent::__construct();
    }
}
