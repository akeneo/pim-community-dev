<?php

namespace Oro\Bundle\DataGridBundle\Provider;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;

/**
 * Provides an interface for classes responsible to load datagrid configuration
 */
interface ConfigurationProviderInterface
{
    /**
     * Checks if this provider can be used to load configuration of a grid with the given name
     *
     * @param string $gridName The name of a datagrid
     *
     * @return bool
     */
    public function isApplicable($gridName);

    /**
     * Returns prepared config for requested datagrid
     *
     * @param string $gridName The name of a datagrid
     *
     * @throws \RuntimeException in case when datagrid configuration not found
     * @return DatagridConfiguration
     */
    public function getConfiguration($gridName);
}
