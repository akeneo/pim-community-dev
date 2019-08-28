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
use Akeneo\Pim\Automation\FranklinInsights\Application\QualityHighlights\ApplyAttributeStructure;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectPendingItemIdentifiersQueryInterface;
use PhpSpec\ObjectBehavior;

class SynchronizeAttributesWithFranklinSpec extends ObjectBehavior
{
    public function it_synchronizes_attributes(
        SelectPendingItemIdentifiersQueryInterface $pendingItemIdentifiersQuery,
        ApplyAttributeStructure $applyAttributeStructure,
        QualityHighlightsProviderInterface $qualityHighlightsProvider
    ) {
        $this->beConstructedWith($pendingItemIdentifiersQuery, $applyAttributeStructure, $qualityHighlightsProvider);

        $pendingItemIdentifiersQuery->getUpdatedAttributeCodes(0, 1)->willReturn([1 => 'size']);
        $pendingItemIdentifiersQuery->getUpdatedAttributeCodes(1, 1)->willReturn([42 => 'height']);
        $pendingItemIdentifiersQuery->getUpdatedAttributeCodes(42, 1)->willReturn([]);
        $applyAttributeStructure->apply(['size'])->shouldBeCalled();
        $applyAttributeStructure->apply(['height'])->shouldBeCalled();

        $pendingItemIdentifiersQuery->getDeletedAttributeCodes(0, 1)->willReturn([3 => 'color']);
        $pendingItemIdentifiersQuery->getDeletedAttributeCodes(3, 1)->willReturn([14 => 'weight']);
        $pendingItemIdentifiersQuery->getDeletedAttributeCodes(14, 1)->willReturn([]);
        $qualityHighlightsProvider->deleteAttribute('color')->shouldBeCalled();
        $qualityHighlightsProvider->deleteAttribute('weight')->shouldBeCalled();

        $this->synchronize(1);
    }
}
