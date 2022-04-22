<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Application\SampleData\GetSampleData;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetSampleDataQuery
{
    public string $fileKey;

    /** @var array<int> */
    public array $columnIndices;

    public string $sheetName;

    public int $productLine;
}
