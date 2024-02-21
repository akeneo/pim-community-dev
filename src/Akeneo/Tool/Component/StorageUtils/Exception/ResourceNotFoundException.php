<?php

namespace Akeneo\Tool\Component\StorageUtils\Exception;

/**
 * Exception thrown when an expected resource has not been found.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ResourceNotFoundException extends \RuntimeException
{
    /**
     * @param string          $objectClassName
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct($objectClassName, $code = 0, \Exception $previous = null)
    {
        $message = sprintf("Can't find resource of type %s", $objectClassName);

        parent::__construct($message, $code, $previous);
    }
}
