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

namespace Akeneo\Platform\TailoredImport\Application\SampleData\GetRefreshedSampleData;

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
            'refreshed_data' => $this->refreshedData,
        ];
    }
}
