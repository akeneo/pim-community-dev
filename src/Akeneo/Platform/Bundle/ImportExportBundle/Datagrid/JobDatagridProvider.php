<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Datagrid;

use Akeneo\Tool\Component\Batch\Job\JobRegistry;

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
     * @var JobRegistry
     */
    protected $registry;

    /**
     * @param JobRegistry $registry
     */
    public function __construct(JobRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Return filter choices for the job column of import grids
     */
    public function getExportJobChoices(): array
    {
        return $this->getJobChoices('export');
    }

    /**
     * Return filter choices for the job column of export grids
     */
    public function getImportJobChoices(): array
    {
        return $this->getJobChoices('import');
    }

    /**
     * Return filter choices for the connector column of import grids
     */
    public function getExportConnectorChoices(): array
    {
        return $this->getConnectorChoices('export');
    }

    /**
     * Return filter choices for the connector column of export grids
     */
    public function getImportConnectorChoices(): array
    {
        return $this->getConnectorChoices('import');
    }

    /**
     * Return filter choices for the job column
     *
     * @param string $type
     */
    protected function getJobChoices(string $type): array
    {
        $choices = [];
        $jobs = $this->registry->allByType($type);

        foreach ($jobs as $job) {
            $choices[$job->getName()] = sprintf('batch_jobs.%s.label', $job->getName());
        }
        asort($choices);

        return array_flip($choices);
    }

    /**
     * Return filter choices for the connector column
     *
     * @param string $type
     *
     * @return array
     */
    protected function getConnectorChoices(string $type)
    {
        $connectors = $this->registry->getConnectors();

        return empty($connectors) ? [] : array_combine($connectors, $connectors);
    }
}
