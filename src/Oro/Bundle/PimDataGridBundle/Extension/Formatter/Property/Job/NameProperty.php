<?php

namespace Oro\Bundle\PimDataGridBundle\Extension\Formatter\Property\Job;

use Akeneo\Tool\Component\Batch\Job\JobRegistry;
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
    /** @var JobRegistry */
    protected $jobRegistry;

    /**
     * @param JobRegistry         $jobRegistry
     * @param TranslatorInterface $translator
     */
    public function __construct(JobRegistry $jobRegistry, TranslatorInterface $translator)
    {
        $this->jobRegistry = $jobRegistry;
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
        $jobs = $this->jobRegistry->all();

        return $jobs;
    }
}
