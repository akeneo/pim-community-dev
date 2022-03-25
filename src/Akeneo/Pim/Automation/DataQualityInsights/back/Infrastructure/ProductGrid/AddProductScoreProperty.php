<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\ProductGrid;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;
use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\AddAdditionalProductProperties;
use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\FetchProductAndProductModelRowsParameters;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AddProductScoreProperty implements AddAdditionalProductProperties
{
    public function __construct(
        private GetProductScoresQueryInterface $getProductScores,
        private EnrichProductAndProductModelRowsWithScores $enrichProductAndProductModelRowsWithScores
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function add(FetchProductAndProductModelRowsParameters $fetchProductAndProductModelRowsParameters, array $rows): array
    {
        if (empty($rows)) {
            return [];
        }

        $productIds = [];
        foreach ($rows as $row) {
            $productIds[] = new ProductId($row->technicalId());
        }

        $scores = $this->getProductScores->byProductIds(ProductIdCollection::fromProductIds($productIds));

        return ($this->enrichProductAndProductModelRowsWithScores)($fetchProductAndProductModelRowsParameters, $rows, $scores);
    }
}
