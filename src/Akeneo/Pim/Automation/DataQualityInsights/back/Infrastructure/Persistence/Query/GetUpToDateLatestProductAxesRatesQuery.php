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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\AxisRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLatestProductAxesRatesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\HasUpToDateEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

final class GetUpToDateLatestProductAxesRatesQuery implements GetLatestProductAxesRatesQueryInterface
{
    /** @var GetLatestProductAxesRatesQueryInterface */
    private $getLatestProductAxesRatesQuery;

    /** @var HasUpToDateEvaluationQueryInterface */
    private $hasUpToDateEvaluationQuery;

    public function __construct(
        GetLatestProductAxesRatesQueryInterface $getLatestProductAxesRatesQuery,
        HasUpToDateEvaluationQueryInterface $hasUpToDateEvaluationQuery
    ) {
        $this->getLatestProductAxesRatesQuery = $getLatestProductAxesRatesQuery;
        $this->hasUpToDateEvaluationQuery = $hasUpToDateEvaluationQuery;
    }

    public function byProductId(ProductId $productId): AxisRateCollection
    {
        if (false === $this->hasUpToDateEvaluationQuery->forProductId($productId)) {
            return new AxisRateCollection();
        }

        return $this->getLatestProductAxesRatesQuery->byProductId($productId);
    }
}
