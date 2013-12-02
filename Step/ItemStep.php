<?php

namespace Oro\Bundle\BatchBundle\Step;

use Symfony\Component\Validator\Constraints as Assert;
use Oro\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Oro\Bundle\BatchBundle\Item\ItemReaderInterface;
use Oro\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Oro\Bundle\BatchBundle\Item\ItemWriterInterface;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;

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
        $itemsToWrite  = array();
        $writeCount    = 0;
        $stopExecution = false;

        $this->initializeStepComponents($stepExecution);

        while (!$stopExecution) {
            // Reading
            try {
                if (null === $item = $this->reader->read()) {
                    $stopExecution = true;

                    continue;
                }
            } catch (InvalidItemException $e) {
                $this->handleStepExecutionWarning($stepExecution, $this->reader, $e);

                continue;
            }

            // Processing
            try {
                $processedItem = $this->processor->process($item);
            } catch (InvalidItemException $e) {
                $this->handleStepExecutionWarning($stepExecution, $this->processor, $e);

                continue;
            }

            // Writing
            if (null !== $processedItem) {
                $itemsToWrite[] = $processedItem;
                $writeCount++;
            }
            if (0 === $writeCount % $this->batchSize) {
                try {
                    $this->writer->write($itemsToWrite);
                    $itemsToWrite = array();
                } catch (InvalidItemException $e) {
                    $this->handleStepExecutionWarning($stepExecution, $this->writer, $e);

                    continue;
                }
            }
        }

        if (count($itemsToWrite) > 0) {
            try {
                $this->writer->write($itemsToWrite);
            } catch (InvalidItemException $e) {
                $this->handleStepExecutionWarning($stepExecution, $this->writer, $e);
            }
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

    /**
     * Handle step execution warning
     *
     * @param StepExecution $stepExecution
     * @param object $element
     * @param InvalidItemException $e
     */
    private function handleStepExecutionWarning(
        StepExecution $stepExecution,
        $element,
        InvalidItemException $e
    ) {
        if ($element instanceof AbstractConfigurableStepElement) {
            $warningName = $element->getName();
        } else {
            $warningName = get_class($element);
        }

        $stepExecution->addWarning($warningName, $e->getMessage(), $e->getItem());
        $this->dispatchInvalidItemEvent(get_class($element), $e->getMessage(), $e->getItem());
    }
}
