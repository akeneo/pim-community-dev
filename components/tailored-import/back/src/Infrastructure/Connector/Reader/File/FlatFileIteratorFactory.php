<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Infrastructure\Connector\Reader\File;

use Akeneo\Platform\TailoredImport\Infrastructure\FlatFileIterator\FlatFileIteratorInterface;
use Akeneo\Platform\TailoredImport\Infrastructure\FlatFileIterator\XlsxFlatFileIterator;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\UndefinedJobParameterException;

class FlatFileIteratorFactory
{
    public function __construct(
        private string $fileType,
    ) {
    }

    public function create(JobParameters $jobParameters): FlatFileIteratorInterface
    {
        $filePath = $jobParameters->get('filePath');

        // TODO remove this try catch when the file structure will be available in the job parameters
        try {
            $fileStructure = $jobParameters->get('file_structure');
        } catch (UndefinedJobParameterException $exception) {
            $fileStructure = [
                'header_line' => 0,
                'header_column' => 0,
                'product_line' => 1,
                'product_column' => 0,
                'sheet_index' => 0,
            ];
        }

        return match ($this->fileType) {
            'xlsx' => new XlsxFlatFileIterator($this->fileType, $filePath, $fileStructure),
            default => throw new \InvalidArgumentException(sprintf('Unsupported file type "%s"', $this->fileType)),
        };
    }
}
