<?php

namespace Pim\Bundle\DataGridBundle\Datagrid\Product;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;

/**
 * Configurator interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ConfiguratorInterface
{
    /**
     * Configure the grid
     *
     * @param DatagridConfiguration $configuration
     */
    public function configure(DatagridConfiguration $configuration);
}
