<?php

namespace Akeneo\Tool\Component\Connector\Exception;

/**
 * Exception thrown when the identifier column is unknown
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MissingIdentifierException extends \Exception
{
    /**
     * {@inheritdoc}
     */
    public function __construct($message = null, $code = null, $previous = null)
    {
        if (null === $message) {
            $message = 'No identifier column.';
        }
        parent::__construct($message, $code, $previous);
    }
}
