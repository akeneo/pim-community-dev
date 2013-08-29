<?php

namespace Pim\Bundle\BatchBundle\Step;

use Symfony\Component\Validator\Constraints as Assert;
use Pim\Bundle\BatchBundle\Entity\StepExecution;
use Pim\Bundle\BatchBundle\Item\ItemReaderInterface;
use Pim\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Pim\Bundle\BatchBundle\Item\ItemWriterInterface;
use Pim\Bundle\BatchBundle\Event\EventInterface;

/**
 * Basic step implementation that read items, process them and write them
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ItemStep extends AbstractStep
{
    /**
     * @var int
     */
    private $batchSize = 100;

    /**
     * @Assert\Valid
     */
    private $reader = null;

    /**
     * @Assert\Valid
     */
    private $writer = null;

    /**
     * @Assert\Valid
     */
    private $processor = null;

    /**
     * Set the batch size
     *
     * @param int $batchSize
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

    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        return array(
            'reader'    => $this->getReader()->getConfiguration(),
            'processor' => $this->getProcessor()->getConfiguration(),
            'writer'    => $this->getWriter()->getConfiguration(),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setConfiguration(array $config)
    {
        $this->getReader()->setConfiguration($config['reader']);
        $this->getProcessor()->setConfiguration($config['processor']);
        $this->getWriter()->setConfiguration($config['writer']);
    }

    /**
     * {@inheritdoc}
     */
    public function doExecute(StepExecution $stepExecution)
    {
        $itemsToWrite = array();
        $writeCount = 0;

        while (($item = $this->reader->read($stepExecution)) !== null) {
            if (false === $item) {
                $this->dispatchStepExecutionEvent(EventInterface::INVALID_READER_EXECUTION, $stepExecution);
                continue;
            }

            if (null !== $processedItem = $this->processor->process($item)) {
                $itemsToWrite[] = $processedItem;
                $writeCount ++;
                if (0 === ($writeCount % $this->batchSize)) {
                    $this->writer->write($itemsToWrite);
                    $itemsToWrite = array();
                }
            }
        }

        if (count($itemsToWrite) > 0) {
            $this->writer->write($itemsToWrite);
        }
    }
}
