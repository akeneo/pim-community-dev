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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetUpdatedProductsWithoutUpToDateEvaluationQueryInterface;

final class CreateMissingCriteriaEvaluations implements CreateMissingCriteriaEvaluationsInterface
{
    /** @var GetUpdatedProductsWithoutUpToDateEvaluationQueryInterface */
    private $getUpdatedProductsWithoutUpToDateEvaluationQuery;

    /** @var CreateCriteriaEvaluations */
    private $createProductsCriteriaEvaluations;

    public function __construct(
        GetUpdatedProductsWithoutUpToDateEvaluationQueryInterface $getUpdatedProductsWithoutUpToDateEvaluationQuery,
        CreateCriteriaEvaluations $createProductsCriteriaEvaluations
    ) {
        $this->getUpdatedProductsWithoutUpToDateEvaluationQuery = $getUpdatedProductsWithoutUpToDateEvaluationQuery;
        $this->createProductsCriteriaEvaluations = $createProductsCriteriaEvaluations;
    }

    public function createForProductsUpdatedSince(\DateTimeImmutable $updatedSince, int $batchSize): void
    {
        foreach ($this->getUpdatedProductsWithoutUpToDateEvaluationQuery->execute($updatedSince, $batchSize) as $productIds) {
            $this->createProductsCriteriaEvaluations->createAll($productIds);
        }
    }
}
