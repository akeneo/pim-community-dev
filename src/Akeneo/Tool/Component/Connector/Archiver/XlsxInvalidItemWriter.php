<?php

namespace Akeneo\Tool\Component\Connector\Archiver;

use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Connector\Reader\File\FileIteratorInterface;

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
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'invalid_xlsx';
    }

    /**
     * {@inheritdoc}
     */
    protected function getFilename(): string
    {
        return 'invalid_items.xlsx';
    }

    /**
     * {@inheritdoc}
     */
    protected function getInputFileIterator(JobParameters $jobParameters): FileIteratorInterface
    {
        $filePath = $jobParameters->get('filePath');
        $fileIterator = $this->fileIteratorFactory->create($filePath);
        $fileIterator->rewind();
        $fileIterator->next();

        return $fileIterator;
    }
}
