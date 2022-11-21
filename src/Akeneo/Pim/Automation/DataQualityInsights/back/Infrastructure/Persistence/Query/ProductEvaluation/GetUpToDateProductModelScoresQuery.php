<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductModelScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\HasUpToDateEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetUpToDateProductModelScoresQuery implements GetProductModelScoresQueryInterface
{
    public function __construct(
        private HasUpToDateEvaluationQueryInterface $hasUpToDateEvaluationQuery,
        private GetProductModelScoresQueryInterface $getProductModelScoresQuery
    ) {
    }

    public function byProductModelId(ProductModelId $productModelId): Read\Scores
    {
        if ($this->hasUpToDateEvaluationQuery->forEntityId($productModelId)) {
            return $this->getProductModelScoresQuery->byProductModelId($productModelId);
        }

        return new Read\Scores(new ChannelLocaleRateCollection(), new ChannelLocaleRateCollection());
    }

    public function byProductModelIdCollection(ProductModelIdCollection $productModelIdCollection): array
    {
        $upToDateProductModels = $this->hasUpToDateEvaluationQuery->forEntityIdCollection($productModelIdCollection);

        if (is_null($upToDateProductModels)) {
            return [];
        }

        Assert::isInstanceOf($upToDateProductModels, ProductModelIdCollection::class);

        return $this->getProductModelScoresQuery->byProductModelIdCollection($upToDateProductModels);
    }
}
