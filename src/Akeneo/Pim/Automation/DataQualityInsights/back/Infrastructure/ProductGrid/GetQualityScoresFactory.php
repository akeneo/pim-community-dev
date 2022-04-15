<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\ProductGrid;

use Akeneo\Pim\Automation\DataQualityInsights\Application\GetEnabledScoresStrategy;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductModelScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetQualityScoresFactory
{
    public function __construct(
        private GetProductScoresQueryInterface $getProductScoresQuery,
        private GetProductModelScoresQueryInterface $getProductModelScoresQuery,
        private GetEnabledScoresStrategy $getEnabledScores,
    ) {
    }

    public function __invoke(ProductEntityIdCollection $productIdCollection, string $type): array
    {
        $scoresByIds = match ($type) {
            'product' => $this->getProductScoresQuery->byProductIds($productIdCollection),
            'product_model' => $this->getProductModelScoresQuery->byProductModelIds($productIdCollection),
            default => throw new \InvalidArgumentException(sprintf('Invalid type %s', $type))
        };

        return array_map(fn (Read\Scores $scores) => ($this->getEnabledScores)($scores), $scoresByIds);
    }
}
