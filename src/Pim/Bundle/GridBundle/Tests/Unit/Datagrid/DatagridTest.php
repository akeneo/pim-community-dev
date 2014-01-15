<?php

namespace Pim\Bundle\GridBundle\Tests\Unit\Datagrid;

use Oro\Bundle\GridBundle\Tests\Unit\Datagrid\DatagridTest as OroDatagridTest;
use Pim\Bundle\GridBundle\Datagrid\Datagrid;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatagridTest extends OroDatagridTest
{
    /**
     * {@inheritdoc}
     */
    protected function createDatagrid($arguments = array())
    {
        $arguments = $this->getDatagridMockArguments($arguments);

        return new Datagrid(
            $arguments['query'],
            $arguments['columns'],
            $arguments['pager'],
            $arguments['formBuilder'],
            $arguments['routeGenerator'],
            $arguments['parameters'],
            $arguments['eventDispatcher']
        );
    }
}
