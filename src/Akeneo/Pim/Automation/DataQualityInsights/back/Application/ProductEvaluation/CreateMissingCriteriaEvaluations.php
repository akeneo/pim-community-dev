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

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency\EvaluateAttributeOptionSpelling;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency\EvaluateAttributeSpelling;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductIdsWithOutdatedAttributeOptionSpellcheckQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductIdsWithOutdatedAttributeSpellcheckQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductIdsWithUpdatedFamilyAttributesListQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetUpdatedProductsWithoutUpToDateEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;

final class CreateMissingCriteriaEvaluations
{
    /** @var GetUpdatedProductsWithoutUpToDateEvaluationQueryInterface */
    private $getUpdatedProductsWithoutUpToDateEvaluationQuery;

    /** @var CreateCriteriaEvaluations */
    private $createProductsCriteriaEvaluations;

    /** @var GetProductIdsWithOutdatedAttributeSpellcheckQueryInterface */
    private $getProductIdsWithOutdatedAttributeSpellcheckQuery;

    /** @var GetProductIdsWithUpdatedFamilyAttributesListQueryInterface */
    private $getProductIdsWithUpdatedFamilyAttributesListQuery;

    /** @var GetProductIdsWithOutdatedAttributeOptionSpellcheckQueryInterface */
    private $getProductIdsWithOutdatedAttributeOptionSpellcheckQuery;

    public function __construct(
        GetUpdatedProductsWithoutUpToDateEvaluationQueryInterface $getUpdatedProductsWithoutUpToDateEvaluationQuery,
        GetProductIdsWithOutdatedAttributeSpellcheckQueryInterface $getProductIdsWithOutdatedAttributeSpellcheckQuery,
        GetProductIdsWithUpdatedFamilyAttributesListQueryInterface $getProductIdsWithUpdatedFamilyAttributesListQuery,
        GetProductIdsWithOutdatedAttributeOptionSpellcheckQueryInterface $getProductIdsWithOutdatedAttributeOptionSpellcheckQuery,
        CreateCriteriaEvaluations $createProductsCriteriaEvaluations
    ) {
        $this->getUpdatedProductsWithoutUpToDateEvaluationQuery = $getUpdatedProductsWithoutUpToDateEvaluationQuery;
        $this->createProductsCriteriaEvaluations = $createProductsCriteriaEvaluations;
        $this->getProductIdsWithOutdatedAttributeSpellcheckQuery = $getProductIdsWithOutdatedAttributeSpellcheckQuery;
        $this->getProductIdsWithUpdatedFamilyAttributesListQuery = $getProductIdsWithUpdatedFamilyAttributesListQuery;
        $this->getProductIdsWithOutdatedAttributeOptionSpellcheckQuery = $getProductIdsWithOutdatedAttributeOptionSpellcheckQuery;
    }

    public function createForProductsUpdatedSince(\DateTimeImmutable $updatedSince, int $batchSize): void
    {
        foreach ($this->getUpdatedProductsWithoutUpToDateEvaluationQuery->execute($updatedSince, $batchSize) as $productIds) {
            $this->createProductsCriteriaEvaluations->createAll($productIds);
        }
    }

    public function createForProductsImpactedByStructureUpdatedSince(\DateTimeImmutable $updatedSince, int $batchSize): void
    {
        $attributeSpellcheckCriterionCode = new CriterionCode(EvaluateAttributeSpelling::CRITERION_CODE);
        $attributeOptionSpellcheckCriterionCode = new CriterionCode(EvaluateAttributeOptionSpelling::CRITERION_CODE);

        foreach ($this->getProductIdsWithOutdatedAttributeSpellcheckQuery->evaluatedSince($updatedSince, $batchSize) as $productIds) {
            $this->createProductsCriteriaEvaluations->create([$attributeSpellcheckCriterionCode], $productIds);
        }

        foreach ($this->getProductIdsWithUpdatedFamilyAttributesListQuery->updatedSince($updatedSince, $batchSize) as $productIds) {
            $this->createProductsCriteriaEvaluations->create([$attributeSpellcheckCriterionCode], $productIds);
        }

        foreach ($this->getProductIdsWithOutdatedAttributeOptionSpellcheckQuery->evaluatedSince($updatedSince, $batchSize) as $productIds) {
            $this->createProductsCriteriaEvaluations->create([$attributeOptionSpellcheckCriterionCode], $productIds);
        }
    }
}
