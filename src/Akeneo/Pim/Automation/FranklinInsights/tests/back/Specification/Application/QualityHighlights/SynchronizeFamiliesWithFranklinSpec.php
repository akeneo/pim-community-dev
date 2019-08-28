<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Application\QualityHighlights;

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\QualityHighlightsProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectPendingItemIdentifiersQueryInterface;
use PhpSpec\ObjectBehavior;

class SynchronizeFamiliesWithFranklinSpec extends ObjectBehavior
{
    public function it_synchronizes_attributes(
        SelectPendingItemIdentifiersQueryInterface $pendingItemIdentifiersQuery,
        QualityHighlightsProviderInterface $qualityHighlightsProvider
    ) {
        $this->beConstructedWith($pendingItemIdentifiersQuery, $qualityHighlightsProvider);

        $pendingItemIdentifiersQuery->getUpdatedFamilyCodes(0, 1)->willReturn([1 => 'headphones']);
        $pendingItemIdentifiersQuery->getUpdatedFamilyCodes(1, 1)->willReturn([42 => 'router']);
        $pendingItemIdentifiersQuery->getUpdatedFamilyCodes(42, 1)->willReturn([]);
        $qualityHighlightsProvider->applyFamilies(['headphones'])->shouldBeCalled();
        $qualityHighlightsProvider->applyFamilies(['router'])->shouldBeCalled();

        $pendingItemIdentifiersQuery->getDeletedFamilyCodes(0, 1)->willReturn([3 => 'accessories']);
        $pendingItemIdentifiersQuery->getDeletedFamilyCodes(3, 1)->willReturn([14 => 'camcorders']);
        $pendingItemIdentifiersQuery->getDeletedFamilyCodes(14, 1)->willReturn([]);
        $qualityHighlightsProvider->deleteFamily('accessories')->shouldBeCalled();
        $qualityHighlightsProvider->deleteFamily('camcorders')->shouldBeCalled();

        $this->synchronize(1);
    }
}
