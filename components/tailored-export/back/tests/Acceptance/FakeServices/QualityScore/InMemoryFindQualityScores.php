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
use Ramsey\Uuid\UuidInterface;

final class InMemoryFindQualityScores implements FindQualityScoresInterface
{
    private array $qualityScores;

    public function addQualityScore(UuidInterface $productUuid, array $qualityScore): void
    {
        $this->qualityScores[$productUuid->toString()] = $qualityScore;
    }

    public function forProduct(UuidInterface $productUuid, string $channel, string $locale): ?string
    {
        return $this->qualityScores[$productUuid->toString()][$channel][$locale] ?? null;
    }
}
