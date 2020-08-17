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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\ProductGrid;

use Akeneo\Pim\Automation\DataQualityInsights\Application\GetProductAxesRatesInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\AddAdditionalProductProperties;
use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\FetchProductAndProductModelRowsParameters;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\AdditionalProperty;

class FetchEnrichmentForProductRows implements AddAdditionalProductProperties
{
    /** @var GetProductAxesRatesInterface */
    private $getProductAxesRates;

    public function __construct(GetProductAxesRatesInterface $getProductAxesRates)
    {
        $this->getProductAxesRates = $getProductAxesRates;
    }

    /**
     * {@inheritdoc}
     */
    public function add(FetchProductAndProductModelRowsParameters $queryParameters, array $rows): array
    {
        if (empty($rows)) {
            return [];
        }

        $productIds = [];
        foreach ($rows as $row) {
            $productIds[] = $row->technicalId();
        }

        $rowsWithAdditionalProperty = [];
        foreach ($rows as $row) {
            $productRates = $this->getProductAxesRates->get(new ProductId($row->technicalId()));

            $enrichmentRate = $productRates['enrichment']['rates'][$queryParameters->channelCode()][$queryParameters->localeCode()] ?? 'N/A';

            $property = new AdditionalProperty('data_quality_insights_enrichment', $enrichmentRate);
            $rowsWithAdditionalProperty[] = $row->addAdditionalProperty($property);
        }

        return $rowsWithAdditionalProperty;
    }
}
