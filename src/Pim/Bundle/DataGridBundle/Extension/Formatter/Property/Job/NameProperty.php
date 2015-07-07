<?php

namespace Pim\Bundle\DataGridBundle\Extension\Formatter\Property\Job;

use Akeneo\Bundle\BatchBundle\Connector\ConnectorRegistry;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\FieldProperty;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Renders the name of the job
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NameProperty extends FieldProperty
{
    /** @var ConnectorRegistry */
    protected $connectorRegistry;

    /**
     * @param ConnectorRegistry   $connectorRegistry
     * @param TranslatorInterface $translator
     */
    public function __construct(ConnectorRegistry $connectorRegistry, TranslatorInterface $translator)
    {
        $this->connectorRegistry = $connectorRegistry;
        parent::__construct($translator);
    }

    /**
     * {@inheritdoc}
     */
    protected function convertValue($value)
    {
        $jobs = $this->getAllJobs();

        if (isset($jobs[$value])) {
            $value = $this->translator->trans($jobs[$value]->getName());
        }

        return $value;
    }

    /**
     * Get the list of jobs that are declared among all the connectors
     *
     * @return array with job alias => job
     */
    protected function getAllJobs()
    {
        $jobs = [];
        $jobTypes = $this->connectorRegistry->getConnectors();

        foreach ($jobTypes as $jobType) {
            $connectorTypes = $this->connectorRegistry->getJobs($jobType);
            foreach ($connectorTypes as $jobsConnectorType) {
                $jobs = array_merge($jobs, $jobsConnectorType);
            }
        }

        return $jobs;
    }
}
