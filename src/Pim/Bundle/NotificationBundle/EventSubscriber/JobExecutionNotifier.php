<?php

namespace Pim\Bundle\NotificationBundle\EventSubscriber;

use Akeneo\Bundle\BatchBundle\Job\BatchStatus;
use Pim\Bundle\NotificationBundle\Manager\UserNotificationManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Akeneo\Bundle\BatchBundle\Event\JobExecutionEvent;
use Akeneo\Bundle\BatchBundle\Event\EventInterface;

/**
 * Job execution notifier
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobExecutionNotifier implements EventSubscriberInterface
{
    /** @var UserNotificationManager */
    protected $manager;

    /**
     * @param UserNotificationManager $manager
     */
    public function __construct(UserNotificationManager $manager)
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
     * @return void
     */
    public function afterJobExecution(JobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();
        $user = $jobExecution->getUser();

        if (null === $user) {
            return;
        }

        //TODO: inject ImportExportBundle\Entity\Repository\JobExecutionRepository directly
//        $this->repository->hasWarnings($jobExecution->getId());

        $stepExecutions = $jobExecution->getStepExecutions();
        $hasWarnings = false;
        foreach ($stepExecutions as $step) {
            if (0 !== $step->getWarnings()->count()) {
                $hasWarnings = true;
                break;
            }
        }

        $exitCode = $jobExecution->getExitStatus()->getExitCode();
        if ($hasWarnings && $exitCode < BatchStatus::FAILED) {
            $status = 'warning';
        } elseif ($exitCode > BatchStatus::STOPPED) {
            $status = 'error';
        } else {
            $status = 'success';
        }

        $options = [
            'route' => sprintf('pim_importexport_%s_execution_show', $jobExecution->getJobInstance()->getType()),
            'routeParams' => [
                'id' => $jobExecution->getId()
            ]
        ];

        $this->manager->notify(
            [$user],
            sprintf('pim_import_export.notification.%s.complete', $jobExecution->getJobInstance()->getType()),
            $status,
            $options
        );
    }
}
