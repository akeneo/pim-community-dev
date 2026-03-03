<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetProductScoresByUuidsQueryInterface
{
    /**
     * Returns collections of product scores indexed by their product uuid
     *
     * @param UuidInterface[] $productUuids
     *
     * @return Read\Scores[]
     */
    public function byProductUuids(array $productUuids): array;

    public function byProductUuid(UuidInterface $uuid): Read\Scores;
}
