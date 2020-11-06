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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\AxisRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetLatestAxesRatesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\HasUpToDateEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

final class GetUpToDateLatestAxesRatesQuery implements GetLatestAxesRatesQueryInterface
{
    /** @var GetLatestAxesRatesQueryInterface */
    private $getLatestProductAxesRatesQuery;

    /** @var HasUpToDateEvaluationQueryInterface */
    private $hasUpToDateEvaluationQuery;

    public function __construct(
        GetLatestAxesRatesQueryInterface $getLatestProductAxesRatesQuery,
        HasUpToDateEvaluationQueryInterface $hasUpToDateEvaluationQuery
    ) {
        $this->getLatestProductAxesRatesQuery = $getLatestProductAxesRatesQuery;
        $this->hasUpToDateEvaluationQuery = $hasUpToDateEvaluationQuery;
    }

    public function byProductId(ProductId $productId): AxisRateCollection
    {
        if (!$this->hasUpToDateEvaluationQuery->forProductId($productId)) {
            return new AxisRateCollection();
        }

        return $this->getLatestProductAxesRatesQuery->byProductId($productId);
    }
}
