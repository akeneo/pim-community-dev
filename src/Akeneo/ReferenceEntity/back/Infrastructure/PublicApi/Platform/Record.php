<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\PublicApi\Platform;

class Record
{
    /** @param array<string, string> $labels */
    public function __construct(
        private string $code,
        private array $labels
    ) {
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getLabels(): array
    {
        return $this->labels;
    }

    public function normalize(): array
    {
        return [
            'code' => $this->code,
            'labels' => $this->labels,
        ];
    }
}
