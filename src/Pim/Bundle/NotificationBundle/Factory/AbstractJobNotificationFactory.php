<?php

namespace Pim\Bundle\NotificationBundle\Factory;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Convenient class to implement common job notification factories behavior
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractJobNotificationFactory implements JobNotificationFactoryInterface
{
    /**
     * Return the job execution status
     *
     * @param JobExecution $jobExecution
     *
     * @return string
     */
    protected function getJobStatus(JobExecution $jobExecution)
    {
        if ($jobExecution->getStatus()->isUnsuccessful()) {
            $status = 'error';
        } else {
            $status = 'success';
            foreach ($jobExecution->getStepExecutions() as $stepExecution) {
                if ($stepExecution->getWarnings()->count()) {
                    $status = 'warning';
                    break;
                }
            }
        }

        return $status;
    }
}
