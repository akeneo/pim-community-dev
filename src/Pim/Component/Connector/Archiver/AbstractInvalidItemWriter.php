<?php

namespace Pim\Component\Connector\Archiver;

use Akeneo\Component\Batch\Item\InvalidItemInterface;
use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Akeneo\Component\Batch\Model\JobExecution;
use Doctrine\Common\Collections\ArrayCollection;
use League\Flysystem\Filesystem;
use Pim\Bundle\ConnectorBundle\EventListener\InvalidItemsCollector;
use Pim\Component\Connector\Reader\File\FileIteratorFactory;
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

    /** @var JobExecution */
    protected $jobExecution;

    /** @var FileIteratorFactory */
    protected $fileIteratorFactory;

    /** @var DefaultValuesProviderInterface */
    protected $defaultValuesProvider;

    /** @var int */
    protected $batchSize = 100;

    /**
     * @param InvalidItemsCollector          $collector
     * @param ItemWriterInterface            $writer
     * @param FileIteratorFactory            $fileIteratorFactory
     * @param Filesystem                     $filesystem
     * @param DefaultValuesProviderInterface $defaultValuesProvider
     * @param string                         $invalidItemFileFormat
     */
    public function __construct(
        InvalidItemsCollector $collector,
        ItemWriterInterface $writer,
        FileIteratorFactory $fileIteratorFactory,
        Filesystem $filesystem,
        DefaultValuesProviderInterface $defaultValuesProvider,
        $invalidItemFileFormat
    ) {
        $this->collector = $collector;
        $this->writer = $writer;
        $this->fileIteratorFactory = $fileIteratorFactory;
        $this->filesystem = $filesystem;
        $this->defaultValuesProvider = $defaultValuesProvider;
        $this->invalidItemFileFormat = $invalidItemFileFormat;
    }

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

        $invalidItemPositions = new ArrayCollection();
        foreach ($this->collector->getInvalidItems() as $invalidItem) {
            if ($invalidItem instanceof InvalidItemInterface) {
                $invalidItemPositions->add($invalidItem->getItemPosition());
            }
        }

        $readJobParameters = $jobExecution->getJobParameters();
        $currentItemPosition = 0;
        $itemsToWrite = [];

        $fileIterator = $this->getInputFileIterator($readJobParameters);

        $this->setupWriter($jobExecution);

        while ($fileIterator->valid()) {
            $readItem = $fileIterator->current();

            $currentItemPosition++;

            if ($invalidItemPositions->contains($currentItemPosition)) {
                $headers = $fileIterator->getHeaders();
                $invalidItem = array_combine($headers, array_slice($readItem, 0, count($headers)));
                if (false !== $invalidItem) {
                    $itemsToWrite[] = $invalidItem;
                }

                $invalidItemPositions->removeElement($currentItemPosition);
            }

            if (count($itemsToWrite) > 0 && 0 === count($itemsToWrite) % $this->batchSize) {
                $this->writer->write($itemsToWrite);
                $itemsToWrite = [];
            }

            if ($invalidItemPositions->isEmpty()) {
                break;
            }

            $fileIterator->next();
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
     * Get the input file iterator to iterate on all the items of the file.
     * The returned FileIteratorInterface instance should be position on the first item of the file.
     *
     * @param JobParameters $jobParameters
     *
     * @return FileIteratorInterface
     */
    abstract protected function getInputFileIterator(JobParameters $jobParameters);
}
