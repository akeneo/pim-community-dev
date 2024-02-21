<?php

namespace Akeneo\Tool\Component\Connector\Step;

use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\AbstractStep;
use Akeneo\Tool\Component\Batch\Step\TrackableStepInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TaskletStep extends AbstractStep implements TrackableStepInterface
{
    public function __construct(
        string $name,
        EventDispatcherInterface $eventDispatcher,
        JobRepositoryInterface $jobRepository,
        protected TaskletInterface $tasklet
    ) {
        parent::__construct($name, $eventDispatcher, $jobRepository);
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecute(StepExecution $stepExecution): void
    {
        $this->tasklet->setStepExecution($stepExecution);
        $this->tasklet->execute();
    }

    public function getTasklet(): TaskletInterface
    {
        return $this->tasklet;
    }

    public function setTasklet(TaskletInterface $tasklet): void
    {
        $this->tasklet = $tasklet;
    }

    public function isTrackable(): bool
    {
        return $this->tasklet instanceof TrackableTaskletInterface && $this->tasklet->isTrackable();
    }
}
