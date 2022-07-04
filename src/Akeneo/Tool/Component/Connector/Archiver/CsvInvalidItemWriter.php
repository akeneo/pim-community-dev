<?php

namespace Akeneo\Tool\Component\Connector\Archiver;

use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Connector\Reader\File\FileIteratorInterface;

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
    public function getName(): string
    {
        return 'invalid_csv';
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
}
