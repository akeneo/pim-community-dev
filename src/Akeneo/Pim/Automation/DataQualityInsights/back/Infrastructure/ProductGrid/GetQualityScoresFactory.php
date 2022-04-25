<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\ProductGrid;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductModelScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetQualityScoresFactory
{
    public function __construct(
        private GetProductScoresQueryInterface $getProductScoresQuery,
        private GetProductModelScoresQueryInterface $getProductModelScoresQuery
    ) {
    }

    public function __invoke(ProductEntityIdCollection $productIdCollection, string $type): array
    {
        switch ($type) {
            case 'product':
                return $this->getProductScoresQuery->byProductUuidCollection($productIdCollection);
            case 'product_model':
                return $this->getProductModelScoresQuery->byProductModelIdCollection($productIdCollection);
            default:
                throw new \InvalidArgumentException(sprintf('Invalid type %s', $type));
        }
    }
}
