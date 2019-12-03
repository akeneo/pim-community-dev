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

namespace Akeneo\Pim\Automation\FranklinInsights\Application\QualityHighlights;

use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Repository\PendingItemsRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\BatchSize;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\Lock;
use Ramsey\Uuid\Uuid;

class PushStructureAndProductsToFranklin
{
    const CONCURRENCY = 5;

    /** @var BatchSize */
    private $concurrency;

    /** @var SynchronizeFamiliesWithFranklin */
    private $synchronizeFamilies;

    /** @var SynchronizeAttributesWithFranklin */
    private $synchronizeAttributes;

    /** @var SynchronizeProductsWithFranklin */
    private $synchronizeProductsWithFranklin;

    /** @var PendingItemsRepositoryInterface */
    private $pendingItemsRepository;

    public function __construct(
        PendingItemsRepositoryInterface $pendingItemsRepository,
        SynchronizeFamiliesWithFranklin $synchronizeFamilies,
        SynchronizeAttributesWithFranklin $synchronizeAttributes,
        SynchronizeProductsWithFranklin $synchronizeProductsWithFranklin
    ) {
        $this->concurrency = new BatchSize(self::CONCURRENCY);
        $this->synchronizeFamilies = $synchronizeFamilies;
        $this->synchronizeAttributes = $synchronizeAttributes;
        $this->synchronizeProductsWithFranklin = $synchronizeProductsWithFranklin;
        $this->pendingItemsRepository = $pendingItemsRepository;
    }

    public function push(BatchSize $batchAttributesSize, BatchSize $batchFamiliesSize, BatchSize $batchProductsSize): void
    {
        $lock = new Lock((Uuid::uuid4())->toString());
        $this->pendingItemsRepository->acquireLock($lock);

        //The following order is important and must not be changed Attributes, then Families, then products.
        $this->synchronizeAttributes->synchronizeUpdatedAttributes($lock, $batchAttributesSize, $this->concurrency);
        $this->synchronizeAttributes->synchronizeDeletedAttributes($lock, $batchAttributesSize);
        $this->synchronizeFamilies->synchronize($lock, $batchFamiliesSize);
        $this->synchronizeProductsWithFranklin->synchronize($lock, $batchProductsSize);

        // TODO: release lock for possible remaining locks (products with family to synchronize)
    }
}
