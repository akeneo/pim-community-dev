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
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Repository\PendingItemsRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\Lock;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Doctrine\QualityHighlights\PendingItemsRepository;
use PhpSpec\ObjectBehavior;

class SynchronizeFamiliesWithFranklinSpec extends ObjectBehavior
{
    public function it_synchronizes_families(
        SelectPendingItemIdentifiersQueryInterface $pendingItemIdentifiersQuery,
        QualityHighlightsProviderInterface $qualityHighlightsProvider,
        PendingItemsRepositoryInterface $pendingItemsRepository
    ) {
        $this->beConstructedWith($pendingItemIdentifiersQuery, $qualityHighlightsProvider, $pendingItemsRepository);

        $lock = new Lock('42922021-cec9-4810-ac7a-ace3584f8671');

        $pendingItemIdentifiersQuery->getUpdatedFamilyCodes($lock, 100)->willReturn(['headphones', 'router']);
        $qualityHighlightsProvider->applyFamilies(['headphones', 'router'])->shouldBeCalled();
        $pendingItemsRepository->removeUpdatedFamilies(['headphones', 'router'], $lock)->shouldBeCalled();

        $pendingItemIdentifiersQuery->getDeletedFamilyCodes($lock, 100)->willReturn(['accessories', 'camcorders']);
        $qualityHighlightsProvider->deleteFamily('accessories')->shouldBeCalled();
        $qualityHighlightsProvider->deleteFamily('camcorders')->shouldBeCalled();
        $pendingItemsRepository->removeDeletedFamilies(['accessories', 'camcorders'], $lock)->shouldBeCalled();

        $this->synchronize($lock, 100);
    }
}
