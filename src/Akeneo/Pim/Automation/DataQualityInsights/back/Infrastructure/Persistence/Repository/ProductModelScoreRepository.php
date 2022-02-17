<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\ProductScoreRepositoryInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelScoreRepository implements ProductScoreRepositoryInterface
{

    /**
     * @inheritDoc
     */
    public function saveAll(array $productsScores): void
    {
        // TODO: Implement saveAll() method.
    }

    public function purgeUntil(\DateTimeImmutable $date): void
    {
        // TODO: Implement purgeUntil() method.
    }
}
