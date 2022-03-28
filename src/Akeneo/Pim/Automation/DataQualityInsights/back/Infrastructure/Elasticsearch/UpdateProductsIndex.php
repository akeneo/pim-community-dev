<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ComputeProductsKeyIndicators;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateProductsIndex
{
    private const PRODUCT_IDENTIFIER_PREFIX = 'product_';

    public function __construct(
        private Client $esClient,
        private GetProductScoresQueryInterface $getProductScoresQuery,
        private ComputeProductsKeyIndicators $getProductsKeyIndicators
    ) {
    }

    public function execute(ProductIdCollection $productIdCollection): void
    {
        $params = [];
        $productsScores = $this->getProductScoresQuery->byProductIds($productIdCollection);
        $productsKeyIndicators = $this->getProductsKeyIndicators->compute($productIdCollection);

        foreach ($productIdCollection->toArray() as $productId) {
            $productId = $productId->toInt();
            if (!array_key_exists($productId, $productsScores)) {
                continue;
            }
            $productScores = $productsScores[$productId];
            $keyIndicators = $productsKeyIndicators[$productId] ?? [];

            $params[self::PRODUCT_IDENTIFIER_PREFIX . $productId] = [
                'script' => [
                    'inline' => "ctx._source.data_quality_insights = params;",
                    'params' => [
                        'scores' => $productScores->toArrayIntRank(),
                        'key_indicators' => $keyIndicators
                    ],
                ]
            ];
        }

        $this->esClient->bulkUpdate(
            array_map(
                fn ($productId) => self::PRODUCT_IDENTIFIER_PREFIX . (string) $productId,
                $productIdCollection->toArrayInt()
            ),
            $params
        );
    }
}
