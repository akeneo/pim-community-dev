<?php

namespace Pim\Bundle\NotificationBundle\EventSubscriber;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Akeneo\Bundle\BatchBundle\Event\EventInterface;
use Akeneo\Bundle\BatchBundle\Event\JobExecutionEvent;
use Pim\Bundle\NotificationBundle\Manager\NotificationManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Intl\Exception\NotImplementedException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Job execution notifier
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobExecutionNotifier implements EventSubscriberInterface
{
    /** @staticvar string */
    const TYPE_MASS_EDIT = 'mass_edit';

    /** @staticvar string */
    const QUICK_EXPORT = 'quick_export';

    /** @var NotificationManagerInterface */
    protected $manager;

    /**
     * @param NotificationManagerInterface $manager
     */
    public function __construct(NotificationManagerInterface $manager)
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
        $this->generateNotification($jobExecution, $user, $type, $status);
    }

    /**
     * Generates the correct notification for the given job $type.
     *
     * @param JobExecution $jobExecution
     * @param null|string  $user
     * @param string       $type
     * @param string       $status
     *
     * @throws NotImplementedException
     */
    protected function generateNotification(JobExecution $jobExecution, $user, $type, $status)
    {
        switch ($type) {
            case JobInstance::TYPE_EXPORT:
            case JobInstance::TYPE_IMPORT:
                $this->generateExportImportNotify($jobExecution, $user, $type, $status);
                break;

            case self::TYPE_MASS_EDIT:
            case self::QUICK_EXPORT:
                $this->generateMassEditNotify($jobExecution, $user, $type, $status);
                break;

            default:
                throw new NotImplementedException(
                    sprintf('Impossible to generate a notification for this unknown type : "%s"', $type)
                );
                break;
        }
    }

    /**
     * @param JobExecution         $jobExecution
     * @param string|UserInterface $user
     * @param string               $type
     * @param string               $status
     */
    protected function generateExportImportNotify(JobExecution $jobExecution, $user, $type, $status)
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
     * @param JobExecution         $jobExecution
     * @param string|UserInterface $user
     * @param string               $type
     * @param string               $status
     */
    protected function generateMassEditNotify(JobExecution $jobExecution, $user, $type, $status)
    {
        $this->manager->notify(
            [$user],
            sprintf('pim_mass_edit.notification.%s.%s', $type, $status),
            $status,
            [
                'route'       => 'pim_enrich_job_tracker_show',
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
