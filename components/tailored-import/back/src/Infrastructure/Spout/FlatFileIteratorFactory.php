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

namespace Akeneo\Platform\TailoredImport\Infrastructure\Spout;

use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\UndefinedJobParameterException;

class FlatFileIteratorFactory
{
    public function __construct(private CellsFormatter $cellsFormatter)
    {
    }

    public function create(string $fileType, JobParameters $jobParameters): FlatFileIteratorInterface
    {
        $filePath = $jobParameters->get('filePath');

        // TODO remove this try catch when the file structure will be available in the job parameters
        try {
            $fileStructure = $jobParameters->get('file_structure');
        } catch (UndefinedJobParameterException) {
            $fileStructure = [
                'header_line' => 0,
                'first_column' => 0,
                'product_line' => 1,
                'sheet_name' => 'Sheet1',
            ];
        }

        return match ($fileType) {
            'xlsx' => new XlsxFlatFileIterator($filePath, $fileStructure, $this->cellsFormatter),
            default => throw new \InvalidArgumentException(sprintf('Unsupported file type "%s"', $fileType)),
        };
    }
}
