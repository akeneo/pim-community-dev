<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Application\SampleData\GetRefreshedSampleData;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetRefreshedSampleDataQuery
{
    public int $indexToChange;

    public array $currentSample;

    public string $fileKey;

    public int $columnIndex;

    public string $sheetName;

    public int $productLine;
}
