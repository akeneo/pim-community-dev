<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductIdsImpactedByAttributeGroupActivationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetUpdatedProductIdsQueryInterface;

final class MarkCriteriaToEvaluate implements MarkCriteriaToEvaluateInterface
{
    /** @var GetUpdatedProductIdsQueryInterface */
    private $getUpdatedProductIdsQuery;

    /** @var GetProductIdsImpactedByAttributeGroupActivationQueryInterface */
    private $getProductIdsImpactedByAttributeGroupActivationQuery;

    /** @var CreateCriteriaEvaluations */
    private $createProductsCriteriaEvaluations;

    public function __construct(
        GetUpdatedProductIdsQueryInterface $getUpdatedProductIdsQuery,
        GetProductIdsImpactedByAttributeGroupActivationQueryInterface $getProductIdsImpactedByAttributeGroupActivationQuery,
        CreateCriteriaEvaluations $createProductsCriteriaEvaluations
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
        foreach ($this->getUpdatedProductIdsQuery->since($updatedSince, $batchSize) as $productIds) {
            $this->createProductsCriteriaEvaluations->createAll($productIds);
        }
    }

    private function markCriteriaForProductsImpactedByAttributeGroupActivationUpdatedSince(\DateTimeImmutable $updatedSince, int $batchSize): void
    {
        foreach ($this->getProductIdsImpactedByAttributeGroupActivationQuery->updatedSince($updatedSince, $batchSize) as $productIds) {
            $this->createProductsCriteriaEvaluations->createAll($productIds);
        }
    }
}
