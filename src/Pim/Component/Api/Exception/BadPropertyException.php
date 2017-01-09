<?php

namespace Pim\Component\Api\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BadPropertyException extends HttpException
{
    /**
     * @param string          $message
     * @param \Exception|null $previous
     * @param int             $code
     */
    public function __construct($message = '', \Exception $previous = null, $code = Response::HTTP_BAD_REQUEST)
    {
        preg_match('|Neither the property "(?P<property>\w+)" nor one of the methods|', $message, $matches);

        if (!empty($matches) && isset($matches['property'])) {
            $message = sprintf(
                'Property "%s" does not exist. Check the standard format documentation.',
                $matches['property']
            );
        }

        parent::__construct($code, $message, $previous);
    }
}
