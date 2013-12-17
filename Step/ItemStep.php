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
        $stepElements = array(
            $this->reader,
            $this->writer,
            $this->processor
        );
        $configuration = array();

        foreach ($stepElements as $stepElement) {
            if ($stepElement instanceof AbstractConfigurableStepElement) {
                foreach ($stepElement->getConfiguration() as $key => $value) {
                    if (!isset($configuration[$key]) || $value) {
                        $configuration[$key] = $value;
                    }
                }
            }
        }

        return $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function setConfiguration(array $config)
    {
        $stepElements = array(
            $this->reader,
            $this->writer,
            $this->processor
        );

        foreach ($stepElements as $stepElement) {
            if ($stepElement instanceof AbstractConfigurableStepElement) {
                $stepElement->setConfiguration($config);
            }
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
            try {
                $item = $this->reader->read();
                if (null === $item) {
                    $stopExecution = true;

                    continue;
                }
            } catch (InvalidItemException $e) {
                $this->handleStepExecutionWarning($stepExecution, $this->reader, $e);

                continue;
            }

            try {
                $processedItem = $this->processor->process($item);
                if (null === $processedItem) {
                    continue;
                }
            } catch (InvalidItemException $e) {
                $this->handleStepExecutionWarning($stepExecution, $this->processor, $e);

                continue;
            }

            $itemsToWrite[] = $processedItem;
            if (0 === ++$writeCount % $this->batchSize) {
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
                $itemsToWrite = array();
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
     * @param StepExecution        $stepExecution
     * @param string               $class
     * @param InvalidItemException $e
     */
    private function handleStepExecutionWarning(
        StepExecution $stepExecution,
        AbstractConfigurableStepElement $element,
        InvalidItemException $e
    ) {
        $stepExecution->addWarning($element->getName(), $e->getMessage(), $e->getItem());
        $this->dispatchInvalidItemEvent(get_class($element), $e->getMessage(), $e->getItem());
    }
}
