<?php

namespace Pim\Bundle\BaseConnectorBundle\Archiver;

use Akeneo\Component\Batch\Item\InvalidItemInterface;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\StepExecution;
use Doctrine\Common\Collections\ArrayCollection;
use League\Flysystem\Filesystem;
use Pim\Bundle\BaseConnectorBundle\EventListener\InvalidItemsCollector;
use Pim\Component\Connector\Reader\File\FileIterator;
use Pim\Component\Connector\Reader\File\FileIteratorFactory;
use Pim\Component\Connector\Writer\File\Csv\Writer;

/**
 * Writer for invalid items coming from import.
 * It writes invalid items (ie. invalid products, families, etc...) into a new file, available for download.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InvalidItemWriter extends AbstractFilesystemArchiver
{
    /** @var JobExecution */
    protected $jobExecution;

    /** @var FileIterator */
    protected $fileIterator;

    /** @var FileIteratorFactory */
    protected $fileIteratorFactory;

    /** @var InvalidItemsCollector */
    protected $collector;

    /** @var Writer */
    protected $writer;

    /** @var int */
    protected $batchSize = 100;

    /** @var DefaultValuesProviderInterface */
    protected $defaultValueProvider;

    /** @var string */
    protected $name;

    /** @var string */
    protected $supportedFormat;

    /**
     * @param InvalidItemsCollector $collector
     * @param Writer                $writer
     * @param FileIteratorFactory   $fileIteratorFactory
     * @param Filesystem            $filesystem
     */
    public function __construct(
        InvalidItemsCollector $collector,
        Writer $writer,
        FileIteratorFactory $fileIteratorFactory,
        Filesystem $filesystem,
        DefaultValuesProviderInterface $defaultValueProvider,
        $name,
        $supportedFormat
    ) {
        $this->collector = $collector;
        $this->writer = $writer;
        $this->fileIteratorFactory = $fileIteratorFactory;
        $this->filesystem = $filesystem;
        $this->defaultValueProvider = $defaultValueProvider;
        $this->name = $name;
        $this->supportedFormat = $supportedFormat;
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

        $invalidLineNumbers = new ArrayCollection();
        foreach ($this->collector->getInvalidItems() as $invalidItem) {
            if ($invalidItem instanceof InvalidItemInterface) {
                $invalidLineNumbers->add($invalidItem->getLineNumber());
            }
        }

        $readJobParameters = $jobExecution->getJobParameters();
        $currentLineNumber = 0;
        $itemsToWrite = [];

        $this->setupWriter($jobExecution);

        $iterator = $this->fileIteratorFactory->create(
            $readJobParameters->get('filePath'),
            $readJobParameters->all()
        );
        $iterator->rewind();

        foreach ($iterator as $readItem) {
            $currentLineNumber++;

            if ($invalidLineNumbers->contains($currentLineNumber)) {
                $itemsToWrite[] = $readItem;
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
            return $this->supportedFormat === $jobExecution->getJobParameters()->get('invalid_items_file_format');
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Setup the writer with a new JobExecution to write the invalid_items file.
     * We need to setup the writer manually because it's usually set up by the ItemStep.
     *
     * @param JobExecution $jobExecution
     */
    protected function setupWriter(JobExecution $jobExecution)
    {
        $fileKey = strtr($this->getRelativeArchivePath($jobExecution), ['%filename%' => 'invalid_items.csv']);
        $this->filesystem->put($fileKey, '');

        $writeParams = $this->defaultValueProvider->getDefaultValues();
        $writeParams['filePath'] = $this->filesystem->getAdapter()->getPathPrefix() . $fileKey;

        $writeJobParameters = new JobParameters($writeParams);
        $writeJobExecution  = new JobExecution();
        $writeJobExecution->setJobParameters($writeJobParameters);

        $stepExecution = new StepExecution('processor', $writeJobExecution);
        $this->writer->setStepExecution($stepExecution);
    }
}
