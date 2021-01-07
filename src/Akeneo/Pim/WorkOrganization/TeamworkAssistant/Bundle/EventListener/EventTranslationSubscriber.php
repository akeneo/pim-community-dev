<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\EventListener;

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Event\ProjectEvent;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Event\ProjectEvents;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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
            EventInterface::AFTER_JOB_EXECUTION => 'projectCalculated',
        ];
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
        $projectCode = $jobParameters->get('project_code');
        $project = $this->projectRepository->findOneByIdentifier($projectCode);

        if (null !== $project) {
            $this->eventDispatcher->dispatch(new ProjectEvent($project), ProjectEvents::PROJECT_CALCULATED);
        }
    }
}
