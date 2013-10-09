<?php

namespace Oro\Bundle\DataGridBundle\Datagrid;

use Symfony\Component\EventDispatcher\EventDispatcher;

class Builder
{
    /** @var string */
    protected $baseDatagridClass;

    /** @var EventDispatcher */
    protected $eventDispatcher;

    public function __construct($baseDatagridClass, EventDispatcher $eventDispatcher)
    {
        $this->baseDatagridClass = $baseDatagridClass;
        $this->eventDispatcher   = $eventDispatcher;
    }

    public function build(array $config)
    {

    }
}
