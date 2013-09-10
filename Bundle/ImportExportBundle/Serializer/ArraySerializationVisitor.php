<?php

namespace Oro\Bundle\ImportExportBundle\Serializer;

use JMS\Serializer\GenericSerializationVisitor;

class ArraySerializationVisitor extends GenericSerializationVisitor
{
    protected $filterNull = false;

    public function getResult()
    {
        return $this->getRoot();
    }
}
