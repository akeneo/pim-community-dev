<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Domain\SampleData;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ReplaceSampleDataInterface
{
    public function fromExtractedColumn(array $extractedColumn,array $currentSample,int $indexToReplace): array;
}