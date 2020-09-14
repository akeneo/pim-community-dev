<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetLatestProductAxesRanksQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\HasUpToDateEvaluationQueryInterface;

final class GetUpToDateLatestProductAxesRanksQuery implements GetLatestProductAxesRanksQueryInterface
{
    /** @var GetLatestProductAxesRanksQueryInterface */
    private $getLatestProductAxesRanksQuery;

    /** @var HasUpToDateEvaluationQueryInterface */
    private $hasUpToDateEvaluationQuery;

    public function __construct(
        GetLatestProductAxesRanksQueryInterface $getLatestProductAxesRanksQuery,
        HasUpToDateEvaluationQueryInterface $hasUpToDateEvaluationQuery
    ) {
        $this->getLatestProductAxesRanksQuery = $getLatestProductAxesRanksQuery;
        $this->hasUpToDateEvaluationQuery = $hasUpToDateEvaluationQuery;
    }

    public function byProductIds(array $productIds): array
    {
        $productIds = $this->hasUpToDateEvaluationQuery->forProductIds($productIds);

        if (empty($productIds)) {
            return [];
        }

        return $this->getLatestProductAxesRanksQuery->byProductIds($productIds);
    }
}
