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

namespace Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Axis\Enrichment;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetLatestAxesRatesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AxisCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

class GetProductAxesRates implements GetProductAxesRatesInterface
{
    /** @var GetLatestAxesRatesQueryInterface */
    private $getLatestProductAxesRatesQuery;

    public function __construct(GetLatestAxesRatesQueryInterface $getLatestProductAxesRatesQuery)
    {
        $this->getLatestProductAxesRatesQuery = $getLatestProductAxesRatesQuery;
    }

    public function get(ProductId $productId): array
    {
        $axesRates = $this->getLatestProductAxesRatesQuery->byProductId($productId);
        $enrichmentRates = $axesRates->get(new AxisCode(Enrichment::AXIS_CODE)) ?? new ChannelLocaleRateCollection();

        return [
            Enrichment::AXIS_CODE => [
                'code' => Enrichment::AXIS_CODE,
                'rates' => $enrichmentRates->toArrayLetter(),
            ],
        ];
    }
}
