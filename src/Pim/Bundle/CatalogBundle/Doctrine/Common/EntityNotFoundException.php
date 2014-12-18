<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Common;

use Doctrine\ORM\EntityNotFoundException as EntityNotFoundExceptionBase;

class EntityNotFoundException extends EntityNotFoundExceptionBase
{
    /**
     * Constructor.
     */
    public function __construct($message = 'Entity was not found.')
    {
        $this->message = $message;
    }
}
