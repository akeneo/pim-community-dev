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

namespace Akeneo\PerformanceAnalytics\Domain\TimeToEnrich;

use Webmozart\Assert\Assert;

final class AverageTimeToEnrichCollection
{
    /**
     * @param iterable<AverageTimeToEnrich> $averageTimeToEnrichList
     */
    private function __construct(private iterable $averageTimeToEnrichList)
    {
        Assert::allIsInstanceOf($this->averageTimeToEnrichList, AverageTimeToEnrich::class);
    }

    /**
     * @param iterable<AverageTimeToEnrich> $averageTimeToEnrichList
     */
    public static function fromList(iterable $averageTimeToEnrichList): AverageTimeToEnrichCollection
    {
        return new AverageTimeToEnrichCollection($averageTimeToEnrichList);
    }

    /**
     * @return array<array{code: string, value: float}>
     */
    public function normalize(): array
    {
        return \array_map(
            static fn (AverageTimeToEnrich $averageTimeToEnrich) => $averageTimeToEnrich->normalize(),
            \is_array($this->averageTimeToEnrichList)
                ? $this->averageTimeToEnrichList
                : \iterator_to_array($this->averageTimeToEnrichList)
        );
    }
}
