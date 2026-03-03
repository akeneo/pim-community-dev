<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetUpdatedEntityIdsQueryInterface
{
    /**
     * @return \Iterator<int, ProductUuidCollection|ProductModelIdCollection>
     */
    public function since(\DateTimeImmutable $updatedSince, int $bulkSize): \Iterator;
}
