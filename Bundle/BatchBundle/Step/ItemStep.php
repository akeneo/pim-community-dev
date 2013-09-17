<?php

namespace Oro\Bundle\BatchBundle\Step;

use Symfony\Component\Validator\Constraints as Assert;

use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\BatchBundle\Item\ItemReaderInterface;
use Oro\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Oro\Bundle\BatchBundle\Item\ItemWriterInterface;
use Oro\Bundle\BatchBundle\Event\EventInterface;
use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Oro\Bundle\BatchBundle\Step\StepExecutionAwareInterface;

/**
 * Basic step implementation that read items, process them and write them
 *
 */
class ItemStep extends AbstractStep
{
    /**
     * @var int
     */
    private $batchSize = 100;

    /**
     * @Assert\Valid
     * @var ItemReaderInterface
     */
    private $reader = null;

    /**
     * @Assert\Valid
     * @var ItemWriterInterface
     */
    private $writer = null;

    /**
     * @Assert\Valid
     * @var ItemProcessorInterface
     */
    private $processor = null;

    /**
     * Set the batch size
     *
     * @param integer $batchSize
     *
     * @return $this
     */
    public function setBatchSize($batchSize)
    {
        $this->batchSize = $batchSize;

        return $this;
    }

    /**
     * Set reader
     *
     * @param ItemReaderInterface $reader
     */
    public function setReader(ItemReaderInterface $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Get reader
     *
     * @return ItemReaderInterface|null
     */
    public function getReader()
    {
        return $this->reader;
    }

    /**
     * Set writer
     * @param ItemWriterInterface $writer
     */
    public function setWriter(ItemWriterInterface $writer)
    {
        $this->writer = $writer;
    }

    /**
     * Get writer
     * @return ItemWriterInterface|null
     */
    public function getWriter()
    {
        return $this->writer;
    }

    /**
     * Set processor
     * @param ItemProcessorInterface $processor
     */
    public function setProcessor(ItemProcessorInterface $processor)
    {
        $this->processor = $processor;
    }

    /**
     * Get processor
     * @return ItemProcessorInterface|null
     */
    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        $configuration = array();

        if ($this->reader instanceof AbstractConfigurableStepElement) {
            $configuration['reader'] = $this->reader->getConfiguration();
        } else {
            $configuration['reader'] = array();
        }

        if ($this->processor instanceof AbstractConfigurableStepElement) {
            $configuration['processor'] = $this->processor->getConfiguration();
        } else {
            $configuration['processor'] = array();
        }

        if ($this->writer instanceof AbstractConfigurableStepElement) {
            $configuration['writer'] = $this->writer->getConfiguration();
        } else {
            $configuration['writer'] = array();
        }

        return $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function setConfiguration(array $config)
    {
        if ($this->reader instanceof AbstractConfigurableStepElement && !empty($config['reader'])) {
            $this->reader->setConfiguration($config['reader']);
        }

        if ($this->processor instanceof AbstractConfigurableStepElement && !empty($config['processor'])) {
            $this->processor->setConfiguration($config['processor']);
        }

        if ($this->writer instanceof AbstractConfigurableStepElement && !empty($config['writer'])) {
            $this->writer->setConfiguration($config['writer']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function doExecute(StepExecution $stepExecution)
    {
        $itemsToWrite = array();
        $writeCount = 0;

        $this->initializeStepComponents($stepExecution);

        while (($item = $this->reader->read($stepExecution)) !== null) {
            if (false === $item) {
                $this->dispatchStepExecutionEvent(EventInterface::INVALID_READER_EXECUTION, $stepExecution);
                continue;
            }

            if (null !== $processedItem = $this->processor->process($item)) {
                $itemsToWrite[] = $processedItem;
                $writeCount++;
                if (0 === $writeCount % $this->batchSize) {
                    $this->writer->write($itemsToWrite);
                    $itemsToWrite = array();
                }
            }
        }

        if (count($itemsToWrite) > 0) {
            $this->writer->write($itemsToWrite);
        }
    }

    /**
     * @param StepExecution $stepExecution
     */
    protected function initializeStepComponents(StepExecution $stepExecution)
    {
        if ($this->reader instanceof StepExecutionAwareInterface) {
            $this->reader->setStepExecution($stepExecution);
        }

        if ($this->processor instanceof StepExecutionAwareInterface) {
            $this->processor->setStepExecution($stepExecution);
        }

        if ($this->writer instanceof StepExecutionAwareInterface) {
            $this->writer->setStepExecution($stepExecution);
        }
    }
}
