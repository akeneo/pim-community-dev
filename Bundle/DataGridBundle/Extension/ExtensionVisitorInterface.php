<?php

namespace Oro\Bundle\DataGridBundle\Extension;

use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;

/** @TODO write PHPDoc */
interface ExtensionVisitorInterface
{
    public function isApplicable(array $config);

    public function visitDatasource(array $config, DatasourceInterface $datasource);

    public function visitResult(array $config, \stdClass $result);

    public function visitMetadata(array $config, \stdClass $data);
}
