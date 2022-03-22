<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Domain\GetSampleData;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PickRandomValues
{
    public static function fromColumnExtract(array $columnValues): array
    {
        shuffle($columnValues);
        return array_slice($columnValues, 0, SampleConfigurationEnum::NUMBER_OF_VALUES);
    }
}