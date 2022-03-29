<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Application\SampleData\GetSampleData;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetSampleDataResult
{
    private function __construct(
        private array $sampleData,
    ) {
    }

    public static function create(array $sampleData): self
    {
        return new self($sampleData);
    }

    public function normalize(): array
    {
        return $this->sampleData;
    }
}
