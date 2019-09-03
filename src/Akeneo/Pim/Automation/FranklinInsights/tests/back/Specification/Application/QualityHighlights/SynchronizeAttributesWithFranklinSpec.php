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
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Repository\PendingItemsRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\Lock;
use PhpSpec\ObjectBehavior;

class SynchronizeAttributesWithFranklinSpec extends ObjectBehavior
{
    public function it_synchronizes_attributes(
        SelectPendingItemIdentifiersQueryInterface $pendingItemIdentifiersQuery,
        ApplyAttributeStructure $applyAttributeStructure,
        QualityHighlightsProviderInterface $qualityHighlightsProvider,
        PendingItemsRepositoryInterface $pendingItemsRepository
    ) {
        $this->beConstructedWith($pendingItemIdentifiersQuery, $applyAttributeStructure, $qualityHighlightsProvider, $pendingItemsRepository);

        $lock = new Lock('42922021-cec9-4810-ac7a-ace3584f8671');

        $pendingItemIdentifiersQuery->getUpdatedAttributeCodes($lock, 100)->willReturn(['size', 'height']);
        $applyAttributeStructure->apply(['size', 'height'])->shouldBeCalled();
        $pendingItemsRepository->removeUpdatedAttributes(['size', 'height'], $lock)->shouldBeCalled();

        $pendingItemIdentifiersQuery->getDeletedAttributeCodes($lock, 100)->willReturn(['color', 'weight']);
        $qualityHighlightsProvider->deleteAttribute('color')->shouldBeCalled();
        $qualityHighlightsProvider->deleteAttribute('weight')->shouldBeCalled();
        $pendingItemsRepository->removeDeletedAttributes(['color', 'weight'], $lock)->shouldBeCalled();

        $this->synchronize($lock, 100);
    }
}
