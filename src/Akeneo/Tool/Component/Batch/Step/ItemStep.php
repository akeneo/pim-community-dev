<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Batch\Step;

use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Item\FlushableInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\JobStopper;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Model\Warning;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Basic step implementation that read items, process them and write them
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class ItemStep extends AbstractStep implements StoppableStepInterface
{
    protected ?ItemReaderInterface $reader = null;
    protected ?ItemProcessorInterface $processor = null;
    protected ?ItemWriterInterface $writer = null;
    protected int $batchSize;
    protected ?StepExecution $stepExecution = null;
    private bool $stoppable = false;
    private ?JobStopper $jobStopper = null;

    public function __construct(
        string $name,
        EventDispatcherInterface $eventDispatcher,
        JobRepositoryInterface $jobRepository,
        ItemReaderInterface $reader,
        ItemProcessorInterface $processor,
        ItemWriterInterface $writer,
        int $batchSize = 100,
        JobStopper $jobStopper = null
    ) {
        parent::__construct($name, $eventDispatcher, $jobRepository);

        $this->reader = $reader;
        $this->processor = $processor;
        $this->writer = $writer;
        $this->jobStopper = $jobStopper;
        $this->batchSize = $batchSize;
    }

    /**
     * Get reader
     *
     * @return ItemReaderInterface
     */
    public function getReader()
    {
        return $this->reader;
    }

    /**
     * Get processor
     *
     * @return ItemProcessorInterface
     */
    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * Get writer
     *
     * @return ItemWriterInterface
     */
    public function getWriter()
    {
        return $this->writer;
    }

    public function setStoppable(bool $stoppable): void
    {
        $this->stoppable = $stoppable;
    }

    /**
     * {@inheritdoc}
     */
    public function doExecute(StepExecution $stepExecution)
    {
        $itemsToWrite = [];
        $batchCount = 0;

        $this->initializeStepElements($stepExecution);

        while (true) {
            try {
                $readItem = $this->reader->read();
                if (null === $readItem) {
                    break;
                }
            } catch (InvalidItemException $e) {
                $this->handleStepExecutionWarning($this->stepExecution, $this->reader, $e);

                continue;
            }

            $batchCount++;

            $processedItem = $this->process($readItem);
            if (null !== $processedItem) {
                $itemsToWrite[] = $processedItem;
            }

            if ($batchCount >= $this->batchSize) {
                if (!empty($itemsToWrite)) {
                    $this->write($itemsToWrite);
                    $itemsToWrite = [];
                }

                $this->getJobRepository()->updateStepExecution($stepExecution);
                $this->dispatchStepExecutionEvent(EventInterface::ITEM_STEP_AFTER_BATCH, $stepExecution);
                $batchCount = 0;
                if ($this->isStoppable && $this->jobStopper->isStopping($stepExecution)) {
                    $this->jobStopper->stop($stepExecution);

                    break;
                }
            }
        }

        if (!empty($itemsToWrite)) {
            $this->write($itemsToWrite);
        }

        if ($batchCount > 0) {
            $this->dispatchStepExecutionEvent(EventInterface::ITEM_STEP_AFTER_BATCH, $stepExecution);
        }

        if ($this->jobStopper->isStopping($stepExecution)) {
            $this->jobStopper->stop($stepExecution);
        }

        $this->flushStepElements();
    }

    /**
     * @param StepExecution $stepExecution
     */
    protected function initializeStepElements(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
        foreach ($this->getStepElements() as $element) {
            if ($element instanceof StepExecutionAwareInterface) {
                $element->setStepExecution($stepExecution);
            }
            if ($element instanceof InitializableInterface) {
                $element->initialize();
            }
        }
    }

    /**
     * Flushes step elements
     */
    public function flushStepElements()
    {
        foreach ($this->getStepElements() as $element) {
            if ($element instanceof FlushableInterface) {
                $element->flush();
            }
        }
    }

    /**
     * @param mixed $readItem
     *
     * @return mixed processed item
     */
    protected function process($readItem)
    {
        try {
            return $this->processor->process($readItem);
        } catch (InvalidItemException $e) {
            $this->handleStepExecutionWarning($this->stepExecution, $this->processor, $e);

            return null;
        }
    }

    /**
     * @param array $processedItems
     */
    protected function write($processedItems)
    {
        try {
            $this->writer->write($processedItems);
        } catch (InvalidItemException $e) {
            $this->handleStepExecutionWarning($this->stepExecution, $this->writer, $e);
        }
    }

    /**
     * Handle step execution warning
     *
     * @param StepExecution        $stepExecution
     * @param mixed                $element
     * @param InvalidItemException $e
     */
    protected function handleStepExecutionWarning(
        StepExecution $stepExecution,
        $element,
        InvalidItemException $e
    ) {
        $warning = new Warning(
            $stepExecution,
            $e->getMessage(),
            $e->getMessageParameters(),
            $e->getItem()->getInvalidData()
        );

        $this->jobRepository->addWarning($warning);

        $this->dispatchInvalidItemEvent(
            get_class($element),
            $e->getMessage(),
            $e->getMessageParameters(),
            $e->getItem()
        );
    }

    /**
     * Get the configurable step elements
     *
     * @return array
     */
    protected function getStepElements()
    {
        return [
            'reader'    => $this->reader,
            'processor' => $this->processor,
            'writer'    => $this->writer
        ];
    }

    private function isStopping(StepExecution $stepExecution): bool
    {
        return $this->stoppable &&
            null !== $this->sqlGetJobExecutionStatus &&
            BatchStatus::STOPPING === $this->sqlGetJobExecutionStatus->getByJobExecutionId(
                $stepExecution->getJobExecution()->getId()
            )->getValue();
    }

    private function stop(StepExecution $stepExecution): void
    {
        $stepExecution->setExitStatus(new ExitStatus(ExitStatus::STOPPED));
        $stepExecution->setStatus(new BatchStatus(BatchStatus::STOPPED));
        $this->getJobRepository()->updateStepExecution($stepExecution);
    }
}
