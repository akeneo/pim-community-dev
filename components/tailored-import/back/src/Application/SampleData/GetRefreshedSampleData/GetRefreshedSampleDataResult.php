<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Application\SampleData\GetRefreshedSampleData;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetRefreshedSampleDataResult
{
    private function __construct(
        private string|null $refreshedData,
    ) {
    }

    public static function create(string|null $refreshedData): self
    {
        return new self($refreshedData);
    }

    public function normalize(): array
    {
        return [
            'refreshed_data' => $this->refreshedData
        ];
    }
}
