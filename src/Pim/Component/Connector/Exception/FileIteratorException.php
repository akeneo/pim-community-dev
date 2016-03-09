<?php

namespace Pim\Component\Connector\Exception;

/**
 * Exception thrown when file iterator has not been initialized
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileIteratorException extends \Exception
{
    /**
     * {@inheritdoc}
     */
    public function __construct($message = null, $code = 0, \Exception $previous = null)
    {
        if (null === $message) {
            $message = 'File iterator has to be initialized.';
        }

        parent::__construct($message, $code, $previous);
    }
}
