<?php

namespace Pim\Bundle\NotificationBundle\Factory;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Pim\Bundle\NotificationBundle\Entity\NotificationInterface;

/**
 * Job notification factory interface
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface JobNotificationFactoryInterface
{
    /**
     * Creates a notification
     *
     * @param JobExecution $jobExecution
     *
     * @return NotificationInterface
     */
    public function createNotification(JobExecution $jobExecution);

    /**
     * Does this factory support the specified job type ?
     *
     * @param $jobType
     *
     * @return bool
     */
    public function supportsJobType($jobType);
}
