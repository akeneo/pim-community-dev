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
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectAttributeCodesFromIdsQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectPendingAttributesIdQueryInterface;
use PhpSpec\ObjectBehavior;

class SynchronizeAttributesWithFranklinSpec extends ObjectBehavior
{
    public function it_synchronizes_attributes(
        SelectPendingAttributesIdQueryInterface $pendingAttributesQuery,
        ApplyAttributeStructure $applyAttributeStructure,
        SelectAttributeCodesFromIdsQueryInterface $selectAttributeCodeFromIdQuery,
        QualityHighlightsProviderInterface $qualityHighlightsProvider
    ) {
        $this->beConstructedWith($pendingAttributesQuery, $applyAttributeStructure, $selectAttributeCodeFromIdQuery, $qualityHighlightsProvider);

        $pendingAttributesQuery->getUpdatedAttributeIds(0, 1)->willReturn([1]);
        $pendingAttributesQuery->getUpdatedAttributeIds(1, 1)->willReturn([42]);
        $pendingAttributesQuery->getUpdatedAttributeIds(2, 1)->willReturn([]);
        $applyAttributeStructure->apply([1])->shouldBeCalled();
        $applyAttributeStructure->apply([42])->shouldBeCalled();

        $pendingAttributesQuery->getDeletedAttributeIds(0, 1)->willReturn([39]);
        $pendingAttributesQuery->getDeletedAttributeIds(1, 1)->willReturn([14]);
        $pendingAttributesQuery->getDeletedAttributeIds(2, 1)->willReturn([]);
        $selectAttributeCodeFromIdQuery->execute([39])->willReturn(['color']);
        $selectAttributeCodeFromIdQuery->execute([14])->willReturn(['weight']);
        $qualityHighlightsProvider->deleteAttribute('color')->shouldBeCalled();
        $qualityHighlightsProvider->deleteAttribute('weight')->shouldBeCalled();

        $this->synchronize(1);
    }
}
