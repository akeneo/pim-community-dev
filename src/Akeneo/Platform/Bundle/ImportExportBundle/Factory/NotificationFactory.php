<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Factory;

use Akeneo\Platform\Bundle\NotificationBundle\Factory\AbstractNotificationFactory;
use Akeneo\Platform\Bundle\NotificationBundle\Factory\NotificationFactoryInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Doctrine\Common\Util\ClassUtils;

/**
 * Factory that creates a notification from a job instance
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotificationFactory extends AbstractNotificationFactory implements NotificationFactoryInterface
{
    /** @var array */
    protected $notificationTypes;

    /** @var string */
    protected $notificationClass;

    /**
     * @param array  $notificationTypes
     * @param string $notificationClass
     */
    public function __construct(array $notificationTypes, $notificationClass)
    {
        $this->notificationTypes = $notificationTypes;
        $this->notificationClass = $notificationClass;
    }

    /**
     * {@inheritdoc}
     */
    public function create($jobExecution)
    {
        if (!$jobExecution instanceof JobExecution) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a Akeneo\Tool\Component\Batch\Model\JobExecution, "%s" provided',
                    ClassUtils::getClass($jobExecution)
                )
            );
        }

        $notification = new $this->notificationClass();
        $type = $jobExecution->getJobInstance()->getType();
        $status = $this->getJobStatus($jobExecution);

        $notification
            ->setType($status)
            ->setMessage(sprintf('pim_import_export.notification.%s.%s', $type, $status))
            ->setMessageParams(['%label%' => $jobExecution->getJobInstance()->getLabel()])
            ->setRoute(sprintf('pim_importexport_%s_execution_show', $type))
            ->setRouteParams(['id' => $jobExecution->getId()])
            ->setContext(['actionType' => $type]);

        return $notification;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($type)
    {
        return in_array($type, $this->notificationTypes);
    }
}
