<?php

namespace Akeneo\Platform\Bundle\NotificationBundle\Factory;

use Akeneo\Tool\Component\Batch\Model\JobExecution;

/**
 * Convenient class to implement common job notification factories behavior
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractNotificationFactory implements NotificationFactoryInterface
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
                if ($stepExecution->getWarnings()->count() > 0) {
                    $status = 'warning';
                    break;
                }
            }
        }

        return $status;
    }
}
