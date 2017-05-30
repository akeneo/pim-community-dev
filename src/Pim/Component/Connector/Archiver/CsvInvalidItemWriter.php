<?php

namespace Pim\Component\Connector\Archiver;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\StepExecution;

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
     * {@inheritdoc}
     */
    protected function getInputFileIterator(JobParameters $jobParameters)
    {
        $filePath = $jobParameters->get('filePath');
        $delimiter = $jobParameters->get('delimiter');
        $enclosure = $jobParameters->get('enclosure');
        $fileIterator = $this->fileIteratorFactory->create($filePath, [
            'reader_options' => [
                'fieldDelimiter' => $delimiter,
                'fieldEnclosure' => $enclosure,
            ]
        ]);
        $fileIterator->rewind();
        $fileIterator->next();

        return $fileIterator;
    }

    /**
     * {@inheritdoc}
     */
    protected function setupWriter(JobExecution $jobExecution)
    {
        $fileKey = strtr($this->getRelativeArchivePath($jobExecution), ['%filename%' => 'invalid_items.csv']);
        $this->filesystem->put($fileKey, '');

        $writeParams = $this->defaultValuesProvider->getDefaultValues();
        $writeParams['filePath'] = $this->filesystem->getAdapter()->getPathPrefix() . $fileKey;
        $writeParams['withHeader'] = true;

        $writeJobParameters = new JobParameters($writeParams);
        $writeJobExecution = new JobExecution();
        $writeJobExecution->setJobParameters($writeJobParameters);

        $stepExecution = new StepExecution('processor', $writeJobExecution);
        $this->writer->setStepExecution($stepExecution);
        $this->writer->initialize();
    }
}
