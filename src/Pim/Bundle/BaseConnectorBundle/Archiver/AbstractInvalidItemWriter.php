<?php

namespace Pim\Bundle\BaseConnectorBundle\Archiver;

use Akeneo\Component\Batch\Item\InvalidItemInterface;
use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\JobExecution;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\BaseConnectorBundle\EventListener\InvalidItemsCollector;
use Pim\Component\Connector\Reader\File\FileIteratorInterface;

/**
 * Mutualizes code for writers
 *
 * @author    Soulet Olivier <olivier.soulet@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractInvalidItemWriter extends AbstractFilesystemArchiver
{
    /** @var ItemWriterInterface */
    protected $writer;

    /** @var InvalidItemsCollector */
    protected $collector;

    /** @var string */
    protected $invalidItemFileFormat;

    /**
     * {@inheritdoc}
     *
     * Re-parse the imported file and write into a new one the invalid lines.
     */
    public function archive(JobExecution $jobExecution)
    {
        if (empty($this->collector->getInvalidItems())) {
            return;
        }

        $invalidLineNumbers = new ArrayCollection();
        foreach ($this->collector->getInvalidItems() as $invalidItem) {
            if ($invalidItem instanceof InvalidItemInterface) {
                $invalidLineNumbers->add($invalidItem->getLineNumber());
            }
        }

        $readJobParameters = $jobExecution->getJobParameters();
        $currentLineNumber = 0;
        $itemsToWrite = [];
        $headers = [];

        $this->setupWriter($jobExecution);

        foreach ($this->getInputFileIterator($readJobParameters) as $readItem) {
            $currentLineNumber++;

            if (1 === $currentLineNumber) {
                $headers = $readItem;
            }

            if ($invalidLineNumbers->contains($currentLineNumber)) {
                $itemsToWrite[] = array_combine($headers, $readItem);
                $invalidLineNumbers->removeElement($currentLineNumber);
            }

            if (count($itemsToWrite) > 0 && 0 === count($itemsToWrite) % $this->batchSize) {
                $this->writer->write($itemsToWrite);
                $itemsToWrite = [];
            }

            if ($invalidLineNumbers->isEmpty()) {
                break;
            }
        }

        if (count($itemsToWrite) > 0) {
            $this->writer->write($itemsToWrite);
        }

        $this->writer->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobExecution $jobExecution)
    {
        if ($jobExecution->getJobParameters()->has('invalid_items_file_format')) {
            return $this->invalidItemFileFormat === $jobExecution->getJobParameters()->get('invalid_items_file_format');
        }

        return false;
    }

    /**
     * Setup the writer with a new JobExecution to write the invalid_items file.
     * We need to setup the writer manually because it's usually set up by the ItemStep.
     *
     * @param JobExecution $jobExecution
     */
    abstract protected function setupWriter(JobExecution $jobExecution);

    /**
     * Get the input file iterator to iterate on all the lines of the file.
     *
     * @param JobParameters $jobParameters
     *
     * @return FileIteratorInterface
     */
    abstract protected function getInputFileIterator(JobParameters $jobParameters);
}
