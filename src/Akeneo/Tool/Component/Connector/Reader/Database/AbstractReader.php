<?php

namespace Akeneo\Tool\Component\Connector\Reader\Database;

use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Item\StatefulInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableItemReaderInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;

/**
 * Abstract reader
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractReader implements ItemReaderInterface, InitializableInterface, StepExecutionAwareInterface, TrackableItemReaderInterface, StatefulInterface
{
    /** @var bool Checks if all objects are sent to the processor */
    protected $isExecuted = false;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var \ArrayIterator */
    protected $results;

    protected array $state;

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if (null !== $result = $this->results->current()) {
            $this->results->next();
            $this->stepExecution->incrementSummaryInfo('read');
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $this->initializeReader();

        if (!isset($state['last_position_read'])) {
            return;
        }

        while ($this->results->key() < $state['last_position_read']) {
            $this->results->next();
        }
    }

    public function totalItems(): int
    {
        return $this->getResults()->count();
    }

    /**
     * @return \ArrayIterator
     */
    abstract protected function getResults();

    private function initializeReader(): void
    {
        if (!$this->isExecuted) {
            $this->isExecuted = true;

            $this->results = $this->getResults();
        }
    }

    public function getState(): array
    {
        return [
            'last_position_read' => $this->results?->key(),
        ];
    }

    public function setState(array $state): void
    {
        $this->state = $state;
    }
}
