<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\HasUpToDateEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetUpToDateProductScoresQuery implements GetProductScoresQueryInterface
{
    private HasUpToDateEvaluationQueryInterface $hasUpToDateEvaluationQuery;

    private GetProductScoresQueryInterface $getProductScoresQuery;

    public function __construct(
        HasUpToDateEvaluationQueryInterface $hasUpToDateEvaluationQuery,
        GetProductScoresQueryInterface      $getProductScoresQuery
    ) {
        $this->hasUpToDateEvaluationQuery = $hasUpToDateEvaluationQuery;
        $this->getProductScoresQuery = $getProductScoresQuery;
    }

    public function byProductUuid(ProductEntityIdInterface $productUuid): Read\Scores
    {
        if ($this->hasUpToDateEvaluationQuery->forEntityId($productUuid)) {
            return $this->getProductScoresQuery->byProductUuid($productUuid);
        }

        return new Read\Scores(new ChannelLocaleRateCollection(), new ChannelLocaleRateCollection());
    }

    public function byProductUuidCollection(ProductEntityIdCollection $productUuidCollection): array
    {
        $upToDateProducts = $this->hasUpToDateEvaluationQuery->forEntityIdCollection($productUuidCollection);

        return is_null($upToDateProducts) ? [] : $this->getProductScoresQuery->byProductUuidCollection($upToDateProducts);
    }
}
