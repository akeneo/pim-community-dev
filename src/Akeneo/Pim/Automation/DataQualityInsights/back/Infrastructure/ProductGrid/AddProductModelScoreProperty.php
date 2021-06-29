<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\ProductGrid;

use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\AddAdditionalProductModelProperties;
use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\FetchProductAndProductModelRowsParameters;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\AdditionalProperty;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AddProductModelScoreProperty implements AddAdditionalProductModelProperties
{
    /**
     * {@inheritdoc}
     */
    public function add(FetchProductAndProductModelRowsParameters $queryParameters, array $rows): array
    {
        $rowsWithAdditionalProperty = [];
        foreach ($rows as $row) {
            $property = new AdditionalProperty('data_quality_insights_score', null);
            $rowsWithAdditionalProperty[] = $row->addAdditionalProperty($property);
        }

        return $rowsWithAdditionalProperty;
    }
}
