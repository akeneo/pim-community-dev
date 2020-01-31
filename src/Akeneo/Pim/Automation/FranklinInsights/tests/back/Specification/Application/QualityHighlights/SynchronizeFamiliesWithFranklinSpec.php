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
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectFamiliesToApplyQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectPendingItemIdentifiersQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Repository\PendingItemsRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\BatchSize;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\Lock;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\BadRequestException;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

class SynchronizeFamiliesWithFranklinSpec extends ObjectBehavior
{
    public function let(
        SelectPendingItemIdentifiersQueryInterface $pendingItemIdentifiersQuery,
        QualityHighlightsProviderInterface $qualityHighlightsProvider,
        PendingItemsRepositoryInterface $pendingItemsRepository,
        SelectFamiliesToApplyQueryInterface $selectFamiliesToApplyQuery,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith($pendingItemIdentifiersQuery, $qualityHighlightsProvider, $pendingItemsRepository, $selectFamiliesToApplyQuery, $logger);
    }

    public function it_synchronizes_families(
        SelectPendingItemIdentifiersQueryInterface $pendingItemIdentifiersQuery,
        QualityHighlightsProviderInterface $qualityHighlightsProvider,
        PendingItemsRepositoryInterface $pendingItemsRepository,
        SelectFamiliesToApplyQueryInterface $selectFamiliesToApplyQuery
    ) {
        $lock = new Lock('42922021-cec9-4810-ac7a-ace3584f8671');
        $families = $this->getFamilies();

        $pendingItemIdentifiersQuery->getUpdatedFamilyCodes($lock, 100)->willReturn(['headphones', 'router']);
        $selectFamiliesToApplyQuery->execute(['headphones', 'router'])->willReturn($families);
        $qualityHighlightsProvider->applyFamilies($families)->shouldBeCalled();
        $pendingItemsRepository->removeUpdatedFamilies(['headphones', 'router'], $lock)->shouldBeCalled();

        $pendingItemIdentifiersQuery->getDeletedFamilyCodes($lock, 100)->willReturn(['accessories', 'camcorders']);
        $qualityHighlightsProvider->deleteFamily('accessories')->shouldBeCalled();
        $qualityHighlightsProvider->deleteFamily('camcorders')->shouldBeCalled();
        $pendingItemsRepository->removeDeletedFamilies(['accessories', 'camcorders'], $lock)->shouldBeCalled();

        $this->synchronize($lock, new BatchSize(100));
    }

    public function it_releases_the_lock_on_exception(
        SelectPendingItemIdentifiersQueryInterface $pendingItemIdentifiersQuery,
        QualityHighlightsProviderInterface $qualityHighlightsProvider,
        PendingItemsRepositoryInterface $pendingItemsRepository,
        SelectFamiliesToApplyQueryInterface $selectFamiliesToApplyQuery
    ) {
        $lock = new Lock('42922021-cec9-4810-ac7a-ace3584f8671');
        $families = $this->getFamilies();

        $pendingItemIdentifiersQuery->getUpdatedFamilyCodes($lock, 100)->willReturn(['headphones', 'router']);
        $selectFamiliesToApplyQuery->execute(['headphones', 'router'])->willReturn($families);
        $qualityHighlightsProvider->applyFamilies($families)->willThrow(new \Exception());
        $pendingItemsRepository->releaseUpdatedFamiliesLock(['headphones', 'router'], $lock)->shouldBeCalled();
        $pendingItemsRepository->removeUpdatedFamilies(['headphones', 'router'], $lock)->shouldNotBeCalled();

        $pendingItemIdentifiersQuery->getDeletedFamilyCodes($lock, 100)->willReturn(['accessories', 'camcorders']);
        $qualityHighlightsProvider->deleteFamily('accessories')->shouldBeCalled();
        $qualityHighlightsProvider->deleteFamily('camcorders')->willThrow(new \Exception());
        $pendingItemsRepository->releaseDeletedFamiliesLock(['accessories', 'camcorders'], $lock)->shouldBeCalled();
        $pendingItemsRepository->removeDeletedFamilies(['accessories', 'camcorders'], $lock)->shouldNotBeCalled();

        $this->synchronize($lock, new BatchSize(100));
    }

    public function it_ignores_bad_request_exception(
        SelectPendingItemIdentifiersQueryInterface $pendingItemIdentifiersQuery,
        QualityHighlightsProviderInterface $qualityHighlightsProvider,
        PendingItemsRepositoryInterface $pendingItemsRepository,
        SelectFamiliesToApplyQueryInterface $selectFamiliesToApplyQuery
    ) {
        $lock = new Lock('42922021-cec9-4810-ac7a-ace3584f8671');
        $families = $this->getFamilies();

        $pendingItemIdentifiersQuery->getUpdatedFamilyCodes($lock, 100)->willReturn(['headphones', 'router']);
        $selectFamiliesToApplyQuery->execute(['headphones', 'router'])->willReturn($families);
        $qualityHighlightsProvider->applyFamilies($families)->willThrow(new BadRequestException());
        $pendingItemsRepository->releaseUpdatedFamiliesLock(['headphones', 'router'], $lock)->shouldBeCalled();
        $pendingItemsRepository->removeUpdatedFamilies(['headphones', 'router'], $lock)->shouldNotBeCalled();

        $pendingItemIdentifiersQuery->getDeletedFamilyCodes($lock, 100)->willReturn(['accessories', 'camcorders']);
        $qualityHighlightsProvider->deleteFamily('accessories')->shouldBeCalled();
        $qualityHighlightsProvider->deleteFamily('camcorders')->willThrow(new BadRequestException());
        $pendingItemsRepository->releaseDeletedFamiliesLock(['accessories', 'camcorders'], $lock)->shouldBeCalled();
        $pendingItemsRepository->removeDeletedFamilies(['accessories', 'camcorders'], $lock)->shouldNotBeCalled();

        $this->synchronize($lock, new BatchSize(100));
    }

    private function getFamilies(): array
    {
        return [
            [
                'code' => 'headphones',
                'attributes' => ['weight', 'color'],
                'labels' => [
                    [
                        'locale' => 'en_US',
                        'label' => 'Headphones',
                    ],
                ],
            ],
            [
                'code' => 'router',
                'attributes' => ['size'],
                'labels' => [],
            ],
        ];
    }
}
