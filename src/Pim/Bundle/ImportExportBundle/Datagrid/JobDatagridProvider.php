<?php

namespace Pim\Bundle\ImportExportBundle\Datagrid;

use Oro\Bundle\BatchBundle\Connector\ConnectorRegistry;

/**
 * Provider for job datagrid choice lists
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobDatagridProvider
{
    /**
     * Connector registry
     *
     * @var ConnectorRegistry
     */
    protected $registry;

    /**
     * @param ConnectorRegistry $registry
     */
    public function __construct(ConnectorRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Return filter choices for the job column of import grids
     *
     * @return array
     */
    public function getExportJobChoices()
    {
        return $this->getJobChoices('export');
    }

    /**
     * Return filter choices for the job column of export grids
     *
     * @return array
     */
    public function getImportJobChoices()
    {
        return $this->getJobChoices('import');
    }

    /**
     * Return filter choices for the connector column of import grids
     *
     * @return array
     */
    public function getExportConnectorChoices()
    {
        return $this->getConnectorChoices('export');
    }

    /**
     * Return filter choices for the connector column of export grids
     *
     * @return array
     */
    public function getImportConnectorChoices()
    {
        return $this->getConnectorChoices('import');
    }

    /**
     * Return filter choices for the job column
     *
     * @param string $type
     *
     * @return array
     */
    protected function getJobChoices($type)
    {
        $jobs = [];
        $registryJobs = $this->registry->getJobs($type);

        foreach ($registryJobs as $registryJob) {
            $jobs = array_merge($jobs, array_keys($registryJob));
        }

        $choices = array_unique($jobs);

        return empty($choices) ? [] : array_combine($choices, $choices);
    }

    /**
     * Return filter choices for the connector column
     *
     * @param string $type
     *
     * @return array
     */
    protected function getConnectorChoices($type)
    {
        $connectors = array_keys($this->registry->getJobs($type));

        return empty($connectors) ? [] : array_combine($connectors, $connectors);
    }
}
