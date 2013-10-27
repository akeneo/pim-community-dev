<?php

namespace Oro\Bundle\DataGridBundle\Extension;

use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;

interface ExtensionVisitorInterface
{
    /**
     * Checks if extensions should be applied to grid
     *
     * @param array $config
     *
     * @return bool
     */
    public function isApplicable(array $config);

    /**
     * Apply changes provided by applied extensions on datasource
     *
     * @param array               $config
     * @param DatasourceInterface $datasource
     *
     * @return mixed
     */
    public function visitDatasource(array $config, DatasourceInterface $datasource);

    /**
     * Apply changes provided by applied extensions on result data
     *
     * @param array     $config
     * @param \stdClass $result
     *
     * @return mixed
     */
    public function visitResult(array $config, \stdClass $result);

    /**
     * Apply changes provided by applied extensions on metadata
     *
     * @param array     $config
     * @param \stdClass $data
     *
     * @return mixed
     */
    public function visitMetadata(array $config, \stdClass $data);

    /**
     * Returns priority needed for applying
     * Format from -255 to 255
     *
     * @return int
     */
    public function getPriority();
}
