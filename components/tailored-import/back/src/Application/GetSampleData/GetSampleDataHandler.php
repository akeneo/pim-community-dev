<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Application\GetSampleData;

use Akeneo\Platform\TailoredImport\Domain\GetSampleData\FillBlankValues;
use Akeneo\Platform\TailoredImport\Domain\GetSampleData\FilterUniqueValues;
use Akeneo\Platform\TailoredImport\Domain\GetSampleData\PickRandomValues;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetSampleDataHandler
{
    public function handle(GetSampleDataQuery $getSampleDataQuery): GetSampleDataResult
    {
        $filteredValues = FilterUniqueValues::fromColumnExtract($getSampleDataQuery->columnValues);
        $sampleData = PickRandomValues::fromColumnExtract($filteredValues);
        $filledSampleData = FillBlankValues::fromSampleData($sampleData);

        return GetSampleDataResult::create($filledSampleData);
    }
}
