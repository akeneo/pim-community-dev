<?php

namespace Pim\Bundle\NotificationBundle\EventSubscriber;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
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
        return [
            EventInterface::AFTER_JOB_EXECUTION => 'afterJobExecution',
        ];
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

        // TODO: maybe create a registry or something similar to load routes ?

        if (JobInstance::TYPE_EXPORT === $type || JobInstance::TYPE_IMPORT === $type) {
            $this->generateExportImportNotify($user, $jobExecution, $type, $status);
        } elseif ('mass_edit' === $type) { //TODO: move this status in job instance const
            $this->generateMassEditNotify($user, $jobExecution, $type, $status);
        }
    }

    /**
     * @param $user
     * @param $jobExecution
     * @param $type
     * @param $status
     */
    protected function generateExportImportNotify($user, $jobExecution, $type, $status)
    {
        $this->manager->notify(
            [$user],
            sprintf('pim_import_export.notification.%s.%s', $type, $status),
            $status,
            [
                'route'         => sprintf('pim_importexport_%s_execution_show', $type),
                'routeParams'   => [
                    'id' => $jobExecution->getId()
                ],
                'messageParams' => [
                    '%label%' => $jobExecution->getJobInstance()->getLabel()
                ]
            ]
        );
    }

    /**
     * @param $user
     * @param $jobExecution
     * @param $type
     * @param $status
     */
    protected function generateMassEditNotify($user, $jobExecution, $type, $status)
    {
        $this->manager->notify(
            [$user],
            sprintf('pim_mass_edit.notification.%s.%s', $type, $status),
            $status,
            [
                'messageParams' => [
                    '%label%'   => $jobExecution->getJobInstance()->getLabel()
                ]
            ]
        );
    }
}
