<?php

namespace Pim\Bundle\BaseConnectorBundle\Archiver;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\StepExecution;
use Pim\Component\Connector\Job\JobParameters\DefaultValuesProvider\ProductCsvExport;
use Pim\Component\Connector\Job\JobParameters\DefaultValuesProvider\SimpleCsvExport;
use Pim\Component\Connector\Reader\File\FileIteratorInterface;

/**
 * Writer for invalid items coming from a CSV import.
 * It writes invalid items (ie. invalid products, families, etc...) into a new CSV file, available for download.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CsvInvalidItemWriter extends AbstractInvalidItemWriter
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'invalid_csv';
    }

    /**
     * Get the input file iterator to iterate on all the lines of the file.
     *
     * @param JobParameters $jobParameters
     *
     * @return FileIteratorInterface
     */
    protected function getInputFileIterator(JobParameters $jobParameters)
    {
        $filePath = $jobParameters->get('filePath');
        $delimiter = $jobParameters->get('delimiter');
        $enclosure = $jobParameters->get('enclosure');
        $fileIterator = $this->fileIteratorFactory->create($filePath, [
            'fieldDelimiter' => $delimiter,
            'fieldEnclosure' => $enclosure
        ]);
        $fileIterator->rewind();

        return $fileIterator;
    }

    /**
     * Setup the writer with a new JobExecution to write the invalid_items file.
     * We need to setup the writer manually because it's usally set up by the ItemStep.
     *
     * @param JobExecution $jobExecution
     */
    protected function setupWriter(JobExecution $jobExecution)
    {
        $fileKey = strtr($this->getRelativeArchivePath($jobExecution), ['%filename%' => 'invalid_items.csv']);
        $this->filesystem->put($fileKey, '');

        $provider = new ProductCsvExport(new SimpleCsvExport([]), []);
        $writeParams = $provider->getDefaultValues();
        $writeParams['filePath'] = $this->filesystem->getAdapter()->getPathPrefix() . $fileKey;
        $writeParams['withHeader'] = true;

        $writeJobParameters = new JobParameters($writeParams);
        $writeJobExecution  = new JobExecution();
        $writeJobExecution->setJobParameters($writeJobParameters);

        $stepExecution = new StepExecution('processor', $writeJobExecution);
        $this->writer->setStepExecution($stepExecution);
        $this->writer->initialize();
    }
}
