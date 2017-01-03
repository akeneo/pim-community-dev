<?php

namespace Pim\Bundle\ImportExportBundle\Datagrid;

use Akeneo\Component\Batch\Job\JobRegistry;
use Pim\Bundle\ImportExportBundle\JobLabel\TranslatedLabelProvider;

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

    /** @var TranslatedLabelProvider */
    protected $labelProvider;

    /**
     * @param JobRegistry              $registry
     * @param TranslatedLabelProvider  $labelProvider
     */
    public function __construct(JobRegistry $registry, TranslatedLabelProvider $labelProvider)
    {
        $this->registry = $registry;
        $this->labelProvider = $labelProvider;
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
        $choices = [];
        $jobs = $this->registry->allByType($type);

        foreach ($jobs as $job) {
            $choices[$job->getName()] = $this->labelProvider->getJobLabel($job->getName());
        }
        asort($choices);

        return $choices;
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
        $connectors = $this->registry->getConnectors();

        return empty($connectors) ? [] : array_combine($connectors, $connectors);
    }
}
