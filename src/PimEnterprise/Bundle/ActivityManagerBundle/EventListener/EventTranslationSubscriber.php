<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ActivityManagerBundle\EventListener;

use Akeneo\Component\Batch\Event\EventInterface;
use Akeneo\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use PimEnterprise\Component\ActivityManager\Event\ProjectEvent;
use PimEnterprise\Component\ActivityManager\Event\ProjectEvents;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Translate technical event in business event.
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class EventTranslationSubscriber implements EventSubscriberInterface
{
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $projectRepository;

    /** @var string */
    protected $projectCalculationJobName;

    /**
     * @param EventDispatcherInterface              $eventDispatcher
     * @param IdentifiableObjectRepositoryInterface $projectRepository
     * @param string                                $projectCalculationJobName
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        IdentifiableObjectRepositoryInterface $projectRepository,
        $projectCalculationJobName
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->projectRepository = $projectRepository;
        $this->projectCalculationJobName = $projectCalculationJobName;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::POST_SAVE            => 'projectSaved',
            EventInterface::AFTER_JOB_EXECUTION => 'projectCalculated',
        ];
    }

    /**
     * After saving the project in the database a "project_saved" event is dispatched.
     *
     * @param GenericEvent $event
     */
    public function projectSaved(GenericEvent $event)
    {
        $project = $event->getSubject();

        if (!$project instanceof ProjectInterface) {
            return;
        }

        $this->eventDispatcher->dispatch(ProjectEvents::PROJECT_SAVED, new ProjectEvent($project));
    }

    /**
     * At the end of the project calculation job a "project_calculated" event is dispatched.
     *
     * @param JobExecutionEvent $jobExecutionEvent
     */
    public function projectCalculated(JobExecutionEvent $jobExecutionEvent)
    {
        $jobExecution = $jobExecutionEvent->getJobExecution();
        $jobInstance = $jobExecution->getJobInstance();

        if ($this->projectCalculationJobName !== $jobInstance->getCode()) {
            return;
        }

        $jobParameters = $jobExecution->getJobParameters();
        $projectId = $jobParameters->get('project_code');
        $project = $this->projectRepository->findOneByIdentifier($projectId);

        $this->eventDispatcher->dispatch(ProjectEvents::PROJECT_CALCULATED, new ProjectEvent($project));
    }
}
