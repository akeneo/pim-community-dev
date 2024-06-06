<?php

namespace Response\Bundle\BigQueryProductImportBundle\Archiver;

use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Connector\Archiver\AbstractInvalidItemWriter;
use Akeneo\Tool\Component\Connector\Reader\File\FileIteratorInterface;

class BigQueryInvalidItemWriter extends AbstractInvalidItemWriter
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'invalid_bigquery';
    }

    /**
     * {@inheritdoc}
     */
    protected function getFilename(): string
    {
        return 'invalid_items.csv';
    }

    /**
     * {@inheritdoc}
     */
    protected function getInputFileIterator(JobParameters $jobParameters): FileIteratorInterface
    {
        $filePath = $jobParameters->get('storage')['file_path'];
        $delimiter = $jobParameters->get('delimiter');
        $enclosure = $jobParameters->get('enclosure');
        $bigQueryDataset = $jobParameters->get('bigquery_dataset');
        $fileIterator = $this->fileIteratorFactory->create($filePath, [
            'reader_options' => [
                'fieldDelimiter' => $delimiter,
                'fieldEnclosure' => $enclosure,
            ],
            'bigquery_dataset' => $bigQueryDataset,
        ]);
        $fileIterator->rewind();
        $fileIterator->next();

        return $fileIterator;
    }
}
