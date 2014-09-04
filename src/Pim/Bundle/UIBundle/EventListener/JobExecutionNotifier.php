<?php

namespace Pim\Bundle\UIBundle\EventListener;

use Pim\Bundle\ImportExportBundle\Entity\Repository\JobExecutionRepository;
use Pim\Bundle\UIBundle\Manager\NotificationManager;
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
    /** @var NotificationManager */
    protected $manager;

    /** @var JobExecutionRepository */
    //protected $repository;

    /**
     * @param NotificationManager $manager
     */
    public function __construct(NotificationManager $manager/*, JobExecutionRepository $repository*/)
    {
        $this->manager    = $manager;
        //$this->repository = $repository;
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
     * @param JobExecutionEvent $event
     */
    public function afterJobExecution(JobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();
        $user = $jobExecution->getUser();

        if (null === $user) {
            return;
        }

        //$this->repository->hasWarnings($jobExecution->getId());

        if ($jobExecution->getExitStatus()->getExitCode() > 4) {
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
            $jobExecution->getExitStatus(),
            $options
        );
    }
}
