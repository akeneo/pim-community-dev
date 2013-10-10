<?php

namespace Oro\Bundle\DataGridBundle\Extension;

use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;

interface ExtensionVisitorInterface
{
    /**
     * @param array $config
     *
     * @return bool
     */
    public function isApplicable(array $config);

    public function visitDatasource(array $config, DatasourceInterface $datasource);

    public function visitResult(array $config, \stdClass $result);
}
