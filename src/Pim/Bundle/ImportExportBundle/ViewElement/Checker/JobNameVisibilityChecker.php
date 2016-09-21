<?php

namespace Pim\Bundle\ImportExportBundle\ViewElement\Checker;

use Pim\Bundle\EnrichBundle\ViewElement\Checker\VisibilityCheckerInterface;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Checks if a view element is visible according to list of job names
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobNameVisibilityChecker implements VisibilityCheckerInterface
{
    /** @var string[] */
    protected $jobNames = [];

    /**
     * Adds a job name as visible
     *
     * @param string $jobName
     */
    public function addJobName($jobName)
    {
        $this->jobNames[] = $jobName;
    }

    /**
     * {@inheritdoc}
     */
    public function isVisible(array $config = [], array $context = [])
    {
        if (!isset($context['jobInstance'])) {
            throw new \InvalidArgumentException('A "jobInstance" should be provided in the context.');
        }

        $jobInstance = $context['jobInstance'];

        $jobNames = $this->jobNames;
        if (isset ($config['job_names'])) {
            $jobNames = array_merge($jobNames, $config['job_names']);
        }

        return in_array($jobInstance->getJobName(), $jobNames);
    }
}
