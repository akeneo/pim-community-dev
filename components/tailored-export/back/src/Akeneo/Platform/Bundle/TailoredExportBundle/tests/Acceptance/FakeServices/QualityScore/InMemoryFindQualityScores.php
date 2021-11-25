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

namespace Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\QualityScore;

use Akeneo\Platform\TailoredExport\Domain\Query\FindQualityScoresInterface;

final class InMemoryFindQualityScores implements FindQualityScoresInterface
{
    private array $qualityScores;

    public function addQualityScore(string $productIdentifier, array $qualityScore): void
    {
        $this->qualityScores[$productIdentifier] = $qualityScore;
    }

    public function forProduct(string $productIdentifier, string $channel, string $locale): ?string
    {
        return $this->qualityScores[$productIdentifier][$channel][$locale] ?? null;
    }
}
