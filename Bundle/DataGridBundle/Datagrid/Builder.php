<?php

namespace Oro\Bundle\DataGridBundle\Datagrid;

class Builder
{
    protected $baseDatagridClass;

    public function __construct($baseDatagridClass)
    {
        $this->baseDatagridClass = $baseDatagridClass;
    }

    public function build(BuilderConfigurationProvider $config)
    {

    }
}
