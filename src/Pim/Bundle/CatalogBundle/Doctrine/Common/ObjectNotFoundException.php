<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Common;

use Doctrine\ORM\EntityNotFoundException as EntityNotFoundExceptionBase;

/**
 * Object not found exception
 *
 * TODO: move it to StorageUtilsBundle
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ObjectNotFoundException extends EntityNotFoundExceptionBase
{
    /**
     * @param string    $message
     * @param integer   $code
     * @param Exception $previous
     */
    public function __construct($message = 'Entity was not found.', $code = 0, $previous = null)
    {
        $this->message  = $message;
        $this->code     = $code;
        $this->previous = $previous;
    }
}
