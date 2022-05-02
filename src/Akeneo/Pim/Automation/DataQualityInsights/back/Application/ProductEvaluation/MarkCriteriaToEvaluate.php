<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetEntityIdsImpactedByAttributeGroupActivationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetUpdatedEntityIdsQueryInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MarkCriteriaToEvaluate implements MarkCriteriaToEvaluateInterface
{
    /** @var GetUpdatedEntityIdsQueryInterface */
    private $getUpdatedProductIdsQuery;

    /** @var GetEntityIdsImpactedByAttributeGroupActivationQueryInterface */
    private $getProductIdsImpactedByAttributeGroupActivationQuery;

    /** @var CreateCriteriaEvaluations */
    private $createProductsCriteriaEvaluations;

    public function __construct(
        GetUpdatedEntityIdsQueryInterface                            $getUpdatedProductIdsQuery,
        GetEntityIdsImpactedByAttributeGroupActivationQueryInterface $getProductIdsImpactedByAttributeGroupActivationQuery,
        CreateCriteriaEvaluations                                    $createProductsCriteriaEvaluations
    ) {
        $this->getUpdatedProductIdsQuery = $getUpdatedProductIdsQuery;
        $this->getProductIdsImpactedByAttributeGroupActivationQuery = $getProductIdsImpactedByAttributeGroupActivationQuery;
        $this->createProductsCriteriaEvaluations = $createProductsCriteriaEvaluations;
    }

    public function forUpdatesSince(\DateTimeImmutable $updatedSince, int $batchSize): void
    {
        $this->markCriteriaForProductsUpdatedSince($updatedSince, $batchSize);
        $this->markCriteriaForProductsImpactedByAttributeGroupActivationUpdatedSince($updatedSince, $batchSize);
    }

    private function markCriteriaForProductsUpdatedSince(\DateTimeImmutable $updatedSince, int $batchSize): void
    {
        foreach ($this->getUpdatedProductIdsQuery->since($updatedSince, $batchSize) as $productIdCollection) {
            $this->createProductsCriteriaEvaluations->createAll($productIdCollection);
        }
    }

    private function markCriteriaForProductsImpactedByAttributeGroupActivationUpdatedSince(\DateTimeImmutable $updatedSince, int $batchSize): void
    {
        foreach ($this->getProductIdsImpactedByAttributeGroupActivationQuery->updatedSince($updatedSince, $batchSize) as $productIdCollection) {
            $this->createProductsCriteriaEvaluations->createAll($productIdCollection);
        }
    }
}
