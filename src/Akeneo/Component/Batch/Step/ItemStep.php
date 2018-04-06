<?php

namespace Akeneo\Component\Batch\Step;

use Akeneo\Component\Batch\Event\EventInterface;
use Akeneo\Component\Batch\Item\FlushableInterface;
use Akeneo\Component\Batch\Item\InitializableInterface;
use Akeneo\Component\Batch\Item\InvalidItemException;
use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Basic step implementation that read items, process them and write them
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class ItemStep extends AbstractStep
{
    /** @var int */
    protected $batchSize;

    /** @var ItemReaderInterface */
    protected $reader = null;

    /** @var ItemWriterInterface */
    protected $writer = null;

    /** @var ItemProcessorInterface */
    protected $processor = null;

    /** @var StepExecution */
    protected $stepExecution = null;

    /**
     * @param string                   $name
     * @param EventDispatcherInterface $eventDispatcher
     * @param JobRepositoryInterface   $jobRepository
     * @param ItemReaderInterface      $reader
     * @param ItemProcessorInterface   $processor
     * @param ItemWriterInterface      $writer
     * @param integer                  $batchSize
     */
    public function __construct(
        $name,
        EventDispatcherInterface $eventDispatcher,
        JobRepositoryInterface $jobRepository,
        ItemReaderInterface $reader,
        ItemProcessorInterface $processor,
        ItemWriterInterface $writer,
        $batchSize = 100
    ) {
        parent::__construct($name, $eventDispatcher, $jobRepository);

        $this->reader = $reader;
        $this->processor = $processor;
        $this->writer = $writer;
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
            }
        }

        if (!empty($itemsToWrite)) {
            $this->write($itemsToWrite);
        }

        if ($batchCount > 0) {
            $this->dispatchStepExecutionEvent(EventInterface::ITEM_STEP_AFTER_BATCH, $stepExecution);
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
        $this->jobRepository->insertWarning(
            $stepExecution,
            $e->getMessage(),
            $e->getMessageParameters(),
            $e->getItem()->getInvalidData()
        );

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
}
