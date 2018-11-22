<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Exception;

/**
 * Object not found exception
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ObjectNotFoundException extends \Exception
{
    /**
     * @param string     $message
     * @param int        $code
     * @param \Exception $previous
     */
    public function __construct($message = 'Object was not found.', $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
