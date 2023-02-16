<?php

namespace Akeneo\Pim\Structure\Component\Exception;

/**
 * Exception raised when trying to remove an attribute used as label for any family
 *
 */
class AttributeRemovalException extends \RuntimeException
{
    public function __construct(
        public readonly string $messageTemplate,
        public readonly array $messageParameters = [],
    ) {
        parent::__construct();
    }
}
