<?php

namespace Pim\Bundle\GridBundle\Datagrid;

use Oro\Bundle\GridBundle\Datagrid\DatagridInterface;

use Oro\Bundle\GridBundle\Datagrid\Datagrid as OroDatagrid;

class Datagrid extends OroDatagrid
{
    protected $serializer;

    public function setSerializer($serializer)
    {
        $this->serializer = $serializer;

        return $this;
    }
}
