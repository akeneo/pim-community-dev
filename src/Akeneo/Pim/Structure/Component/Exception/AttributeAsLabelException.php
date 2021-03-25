<?php

namespace Akeneo\Pim\Structure\Component\Exception;

/**
 * Exception raised when trying to remove an attribute used as label for any family
 *
 */
class AttributeAsLabelException extends \Exception
{
    /**
     * @param string $message
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct(
        string $message,
        $code = 422,
        \Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
