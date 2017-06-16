<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\CatalogRule\Factory;

use Akeneo\Component\Batch\Model\JobExecution;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\NotificationBundle\Factory\AbstractNotificationFactory;
use Pim\Bundle\NotificationBundle\Factory\NotificationFactoryInterface;

/**
 * Factory that creates a notification for rules from a job instance
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class RuleNotificationFactory extends AbstractNotificationFactory implements NotificationFactoryInterface
{
    /** @var string[] */
    protected $notificationTypes;

    /** @var string */
    protected $notificationClass;

    /**
     * @param string[] $notificationTypes
     * @param string   $notificationClass
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
                    'Expects a Akeneo\Component\Batch\Model\JobExecution, "%s" provided',
                    ClassUtils::getClass($jobExecution)
                )
            );
        }

        $notification = new $this->notificationClass();
        $type = $jobExecution->getJobInstance()->getType();
        $status = $this->getJobStatus($jobExecution);

        $notification
            ->setType($status)
            ->setMessage(sprintf('pimee_catalog_rule.notification.%s.%s', $type, $status))
            ->setMessageParams(['%label%' => $jobExecution->getJobInstance()->getLabel()])
            ->setRoute('pim_enrich_job_tracker_show')
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
