<?php

namespace Oro\Bundle\PimDataGridBundle\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\Builder;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration;
use Oro\Bundle\DataGridBundle\Extension\Sorter\Configuration as SorterConfiguration;
use Oro\Bundle\PimDataGridBundle\Datasource\DatasourceTypes;

/**
 * Configure the sorters of the datagrids
 * TODO: find a way to override or merge grids' configurations to remove this listener
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfigureSortersListener
{
    /**
     * Reconfigure sorters
     *
     * @param BuildBefore $event
     */
    public function onBuildBefore(BuildBefore $event)
    {
        $config = $event->getConfig();
        $sortersPath = sprintf('%s[%s]', SorterConfiguration::SORTERS_PATH, Configuration::COLUMNS_KEY);
        $sorters = $config->offsetGetByPath($sortersPath);

        $datasourceType = $config->offsetGetByPath(Builder::DATASOURCE_TYPE_PATH);
        $sorterType = null;

        if (DatasourceTypes::DATASOURCE_PRODUCT === $datasourceType) {
            $sorterType = 'product_field';
        }

        if (null === $sorterType) {
            return;
        }

        foreach ($sorters as $sorterName => $sorterConfig) {
            if (!isset($sorterConfig['sorter'])) {
                $config->offsetSetByPath(sprintf('%s[%s][sorter]', $sortersPath, $sorterName), $sorterType);
            }
        }
    }
}
