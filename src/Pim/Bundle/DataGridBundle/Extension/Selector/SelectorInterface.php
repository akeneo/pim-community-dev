<?php

namespace Pim\Bundle\DataGridBundle\Extension\Selector;

use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;

/**
 * Selector interface, allows to select some extra data in the datasource (for instance join in case or ORM)
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface SelectorInterface
{
    /**
     * Apply the selector on the datasource
     *
     * @param DatasourceInterface   $datasource
     * @param DatagridConfiguration $configuration
     */
    public function apply(DatasourceInterface $datasource, DatagridConfiguration $configuration);
}
