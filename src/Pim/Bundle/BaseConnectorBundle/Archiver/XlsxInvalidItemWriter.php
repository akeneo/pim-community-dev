<?php

namespace Pim\Bundle\BaseConnectorBundle\Archiver;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\StepExecution;
use League\Flysystem\Filesystem;
use Pim\Bundle\BaseConnectorBundle\EventListener\InvalidItemsCollector;
use Pim\Component\Connector\Job\JobParameters\DefaultValuesProvider\ProductXlsxExport;
use Pim\Component\Connector\Job\JobParameters\DefaultValuesProvider\SimpleXlsxExport;
use Pim\Component\Connector\Reader\File\FileIteratorFactory;
use Pim\Component\Connector\Writer\File\Xlsx\Writer;

/**
 * Writer for invalid items coming from a XLSX import.
 * It writes invalid items (ie. invalid products, families, etc...) into a new XLSX file, available for download.
 *
 * @author    Soulet Olivier <olivier.soulet@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class XlsxInvalidItemWriter extends AbstractInvalidItemWriter
{
    /** @var JobExecution */
    protected $jobExecution;

    /** @var FileIteratorFactory */
    protected $fileIteratorFactory;

    /** @var int */
    protected $batchSize = 100;

    /**
     * @param InvalidItemsCollector $collector
     * @param Writer                $writer
     * @param FileIteratorFactory   $fileIteratorFactory
     * @param Filesystem            $filesystem
     * @param                       $invalidItemFileFormat
     */
    public function __construct(
        InvalidItemsCollector $collector,
        Writer $writer,
        FileIteratorFactory $fileIteratorFactory,
        Filesystem $filesystem,
        $invalidItemFileFormat
    ) {
        $this->collector = $collector;
        $this->writer = $writer;
        $this->fileIteratorFactory = $fileIteratorFactory;
        $this->filesystem = $filesystem;
        $this->invalidItemFileFormat = $invalidItemFileFormat;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'invalid_xlsx';
    }

    /**
     * {@inheritdoc}
     */
    protected function getInputFileIterator(JobParameters $jobParameters)
    {
        $filePath = $jobParameters->get('filePath');
        $fileIterator = $this->fileIteratorFactory->create($filePath);
        $fileIterator->rewind();

        return $fileIterator;
    }

    /**
     * {@inheritdoc}
     */
    protected function setupWriter(JobExecution $jobExecution)
    {
        $fileKey = strtr($this->getRelativeArchivePath($jobExecution), ['%filename%' => 'invalid_items.xlsx']);
        $this->filesystem->put($fileKey, '');

        $provider = new ProductXlsxExport(new SimpleXlsxExport([]), []);
        $writeParams = $provider->getDefaultValues();
        $writeParams['filePath'] = $this->filesystem->getAdapter()->getPathPrefix() . $fileKey;
        $writeParams['withHeader'] = true;

        $writeJobParameters = new JobParameters($writeParams);
        $writeJobExecution  = new JobExecution();
        $writeJobExecution->setJobParameters($writeJobParameters);

        $stepExecution = new StepExecution('processor', $writeJobExecution);
        $this->writer->setStepExecution($stepExecution);
    }
}
