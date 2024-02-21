<?php

namespace Oro\Bundle\DataGridBundle\Extension;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Common\MetadataIterableObject;
use Oro\Bundle\DataGridBundle\Datagrid\Common\ResultsIterableObject;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;

interface ExtensionVisitorInterface
{
    /**
     * Checks if extensions should be applied to grid
     *
     * @param DatagridConfiguration $config
     *
     * @return bool
     */
    public function isApplicable(DatagridConfiguration $config);

    /**
     * Process configuration object
     * Validation and passing default values goes here
     *
     * @param DatagridConfiguration $config
     *
     * @return void
     */
    public function processConfigs(DatagridConfiguration $config);

    /**
     * Apply changes provided by applied extensions on datasource
     *
     * @param DatagridConfiguration $config
     * @param DatasourceInterface   $datasource
     *
     * @return mixed
     */
    public function visitDatasource(DatagridConfiguration $config, DatasourceInterface $datasource);

    /**
     * Apply changes provided by applied extensions on result data
     *
     * @param DatagridConfiguration $config
     * @param ResultsIterableObject         $result
     *
     * @return mixed
     */
    public function visitResult(DatagridConfiguration $config, ResultsIterableObject $result);

    /**
     * Apply changes provided by applied extensions on metadata
     *
     * @param DatagridConfiguration $config
     * @param MetadataIterableObject        $data
     *
     * @return mixed
     */
    public function visitMetadata(DatagridConfiguration $config, MetadataIterableObject $data);

    /**
     * Returns priority needed for applying
     * Format from -255 to 255
     *
     * @return int
     */
    public function getPriority();
}
