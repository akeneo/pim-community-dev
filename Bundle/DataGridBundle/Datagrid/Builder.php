<?php

namespace Oro\Bundle\DataGridBundle\Datagrid;

use Symfony\Component\EventDispatcher\EventDispatcher;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;

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
        $datagrid = new $this->baseDatagridClass();

        $event = new BuildBefore($datagrid, $config);
        $this->eventDispatcher->dispatch(BuildBefore::NAME, $event);
        $config = $event->getConfig();

    }

    protected function getBaseDatagridClass(array $config)
    {
        return !empty($config['options']['base_datagrid_class'])
            ? $config['options']['base_datagrid_class'] : $this->baseDatagridClass;
    }
}
