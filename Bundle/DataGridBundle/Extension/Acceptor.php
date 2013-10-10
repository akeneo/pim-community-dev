<?php

namespace Oro\Bundle\DataGridBundle\Extension;

use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;

class Acceptor
{
    /**
     * @param ExtensionVisitorInterface[] $extensions
     * @param DatagridInterface           $grid
     * @return void
     */
    public function acceptDatasourceVisitors(array $extensions, DatagridInterface $grid)
    {
        foreach ($extensions as $extension) {
            $extension->visitDatasource($grid->getDatasource());
        }
    }

    /**
     * @param ExtensionVisitorInterface[] $extensions
     * @param \stdClass                   $result
     *
     * @return void
     */
    public function acceptResult(array $extensions, \stdClass $result)
    {
        foreach ($extensions as $extension) {
            $extension->visitResult($result);
        }
    }
}
