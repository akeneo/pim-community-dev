<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetEntityIdsToEvaluateQueryInterface
{
    public const BULK_SIZE = 100;

    /**
     * @return \Iterator<int, ProductEntityIdCollection>
     */
    public function execute(?int $limit = null, int $bulkSize = self::BULK_SIZE): \Iterator;
}
