<?php

namespace Akeneo\Pim\Enrichment\Component\Exception;

/**
 * Exception raises when try to remove an attribute used as label for any family
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
