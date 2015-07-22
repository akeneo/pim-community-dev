<?php

namespace Pim\Bundle\NotificationBundle\EventSubscriber;

use Akeneo\Bundle\BatchBundle\Event\EventInterface;
use Akeneo\Bundle\BatchBundle\Event\JobExecutionEvent;
use Pim\Bundle\NotificationBundle\Manager\NotificationManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Job execution notifier
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobExecutionNotifier implements EventSubscriberInterface
{
    /** @var NotificationManager */
    protected $manager;

    /**
     * @param NotificationManager $manager
     */
    public function __construct(NotificationManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            EventInterface::AFTER_JOB_EXECUTION => 'afterJobExecution',
        );
    }

    /**
     * Notify a user of the end of the job
     *
     * @param JobExecutionEvent $event
     *
     * @return null
     */
    public function afterJobExecution(JobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();
        $user = $jobExecution->getUser();

        if (null === $user) {
            return;
        }

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

        $type = $jobExecution->getJobInstance()->getType();

        $this->manager->notify(
            [$user],
            sprintf('pim_import_export.notification.%s.%s', $type, $status),
            $status,
            [
                'route'       => sprintf('pim_importexport_%s_execution_show', $type),
                'routeParams' => [
                    'id' => $jobExecution->getId()
                ],
                'messageParams' => [
                    '%label%' => $jobExecution->getJobInstance()->getLabel()
                ]
            ]
        );
    }
}
