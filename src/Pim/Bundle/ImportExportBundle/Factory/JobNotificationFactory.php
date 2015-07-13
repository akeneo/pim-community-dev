<?php

namespace Pim\Bundle\ImportExportBundle\Factory;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Pim\Bundle\NotificationBundle\Factory\AbstractJobNotificationFactory;
use Pim\Bundle\NotificationBundle\Factory\JobNotificationFactoryInterface;

/**
 * Factory that creates a notification from a job instance
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobNotificationFactory extends AbstractJobNotificationFactory implements JobNotificationFactoryInterface
{
    /** @var string */
    protected $notificationClass;

    public function __construct($notificationClass)
    {
        $this->notificationClass = $notificationClass;
    }

    /**
     * @inheritdoc
     */
    public function createNotification(JobExecution $jobExecution)
    {
        $notification = new $this->notificationClass();
        $type         = $jobExecution->getJobInstance()->getType();
        $status       = $this->getJobStatus($jobExecution);

        $notification
            ->setType($type)
            ->setMessage(sprintf('pim_import_export.notification.%s.%s', $type, $status))
            ->setMessageParams(['%label%' => $jobExecution->getJobInstance()->getLabel()])
            ->setRoute(sprintf('pim_importexport_%s_execution_show', $type))
            ->setRouteParams(['id' => $jobExecution->getId()]);

        return $notification;
    }

    /**
     * @inheritdoc
     */
    public function supportsJobType($jobType)
    {
        return in_array($jobType, [JobInstance::TYPE_IMPORT, JobInstance::TYPE_EXPORT]);
    }
}
