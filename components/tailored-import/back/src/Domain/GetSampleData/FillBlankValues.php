<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Domain\GetSampleData;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FillBlankValues
{
    public static function fromSampleData(array $sampleData): array
    {
        return array_pad($sampleData, SampleConfigurationEnum::NUMBER_OF_VALUES, null);
    }
}