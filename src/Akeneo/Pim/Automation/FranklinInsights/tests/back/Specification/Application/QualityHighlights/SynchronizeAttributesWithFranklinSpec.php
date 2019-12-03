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
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectAttributesToApplyQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectPendingItemIdentifiersQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Repository\PendingItemsRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\BatchSize;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\Lock;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\BadRequestException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SynchronizeAttributesWithFranklinSpec extends ObjectBehavior
{
    public function let(
        SelectPendingItemIdentifiersQueryInterface $pendingItemIdentifiersQuery,
        SelectAttributesToApplyQueryInterface $selectAttributesToApplyQuery,
        QualityHighlightsProviderInterface $qualityHighlightsProvider,
        PendingItemsRepositoryInterface $pendingItemsRepository
    ) {
        $this->beConstructedWith($pendingItemIdentifiersQuery, $selectAttributesToApplyQuery, $qualityHighlightsProvider, $pendingItemsRepository);
    }

    public function it_synchronizes_updated_attributes(
        SelectPendingItemIdentifiersQueryInterface $pendingItemIdentifiersQuery,
        SelectAttributesToApplyQueryInterface $selectAttributesToApplyQuery,
        QualityHighlightsProviderInterface $qualityHighlightsProvider
    ) {
        $lock = new Lock('42922021-cec9-4810-ac7a-ace3584f8671');
        $batchSize = new BatchSize(3);
        $concurrency = new BatchSize(2);

        $pendingItemIdentifiersQuery->getUpdatedAttributeCodes($lock, 6)->willReturn(
            ['sku', 'name', 'description', 'brand', 'width', 'height'],
            ['length', 'weight']
        );

        $selectAttributesToApplyQuery->execute(Argument::any())->willReturn([])->shouldBeCalledTimes(3);
        $qualityHighlightsProvider->applyAsyncAttributeStructure(Argument::any())->shouldBeCalledTimes(2);

        $this->synchronizeUpdatedAttributes($lock, $batchSize, $concurrency);
    }

    public function it_does_nothing_if_there_is_no_updated_attributes(
        SelectPendingItemIdentifiersQueryInterface $pendingItemIdentifiersQuery,
        QualityHighlightsProviderInterface $qualityHighlightsProvider
    ) {
        $lock = new Lock('42922021-cec9-4810-ac7a-ace3584f8671');
        $batchSize = new BatchSize(3);
        $concurrency = new BatchSize(2);

        $pendingItemIdentifiersQuery->getUpdatedAttributeCodes($lock, 6)->willReturn([]);
        $qualityHighlightsProvider->applyAsyncAttributeStructure(Argument::any())->shouldNotBeCalled();

        $this->synchronizeUpdatedAttributes($lock, $batchSize, $concurrency);
    }

    public function it_synchronizes_deleted_attributes(
        SelectPendingItemIdentifiersQueryInterface $pendingItemIdentifiersQuery,
        QualityHighlightsProviderInterface $qualityHighlightsProvider,
        PendingItemsRepositoryInterface $pendingItemsRepository
    ) {
        $lock = new Lock('42922021-cec9-4810-ac7a-ace3584f8671');
        $batchSize = new BatchSize(100);

        $pendingItemIdentifiersQuery->getDeletedAttributeCodes($lock, 100)->willReturn(['color', 'weight']);
        $qualityHighlightsProvider->deleteAttribute('color')->shouldBeCalled();
        $qualityHighlightsProvider->deleteAttribute('weight')->shouldBeCalled();
        $pendingItemsRepository->removeDeletedAttributes(['color', 'weight'], $lock)->shouldBeCalled();

        $this->synchronizeDeletedAttributes($lock, $batchSize);
    }

    public function it_releases_the_lock_on_exception_when_synchronizing_deleted_attributes(
        SelectPendingItemIdentifiersQueryInterface $pendingItemIdentifiersQuery,
        QualityHighlightsProviderInterface $qualityHighlightsProvider,
        PendingItemsRepositoryInterface $pendingItemsRepository
    ) {
        $lock = new Lock('42922021-cec9-4810-ac7a-ace3584f8671');
        $batchSize = new BatchSize(100);

        $pendingItemIdentifiersQuery->getDeletedAttributeCodes($lock, 100)->willReturn(['color', 'weight']);
        $qualityHighlightsProvider->deleteAttribute('color')->shouldBeCalled();
        $qualityHighlightsProvider->deleteAttribute('weight')->willThrow(new \Exception());
        $pendingItemsRepository->releaseDeletedAttributesLock(['color', 'weight'], $lock)->shouldBeCalled();
        $pendingItemsRepository->removeDeletedAttributes(['color', 'weight'], $lock)->shouldNotBeCalled();

        $this->synchronizeDeletedAttributes($lock, $batchSize);
    }

    public function it_ignores_bad_request_exception_when_synchronizing_deleted_attributes(
        SelectPendingItemIdentifiersQueryInterface $pendingItemIdentifiersQuery,
        QualityHighlightsProviderInterface $qualityHighlightsProvider,
        PendingItemsRepositoryInterface $pendingItemsRepository
    ) {
        $lock = new Lock('42922021-cec9-4810-ac7a-ace3584f8671');
        $batchSize = new BatchSize(100);

        $pendingItemIdentifiersQuery->getDeletedAttributeCodes($lock, 100)->willReturn(['color', 'weight']);
        $qualityHighlightsProvider->deleteAttribute('color')->shouldBeCalled();
        $qualityHighlightsProvider->deleteAttribute('weight')->willThrow(new BadRequestException());
        $pendingItemsRepository->releaseDeletedAttributesLock(['color', 'weight'], $lock)->shouldNotBeCalled();
        $pendingItemsRepository->removeDeletedAttributes(['color', 'weight'], $lock)->shouldBeCalled();

        $this->synchronizeDeletedAttributes($lock, $batchSize);
    }
}
