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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency\EvaluateAttributeOptionSpelling;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency\EvaluateAttributeSpelling;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CreateCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductIdsWithOutdatedAttributeOptionSpellcheckQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductIdsWithOutdatedAttributeSpellcheckQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductIdsWithUpdatedFamilyAttributesListQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetUpdatedProductIdsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use PhpSpec\ObjectBehavior;

final class PimEnterpriseCreateMissingCriteriaEvaluationsSpec extends ObjectBehavior
{
    public function let(
        GetUpdatedProductIdsQueryInterface $getUpdatedProductIdsQuery,
        GetProductIdsWithOutdatedAttributeSpellcheckQueryInterface $getProductIdsWithOutdatedAttributeSpellcheckQuery,
        GetProductIdsWithUpdatedFamilyAttributesListQueryInterface $getProductIdsWithUpdatedFamilyAttributesListQuery,
        GetProductIdsWithOutdatedAttributeOptionSpellcheckQueryInterface $getProductIdsWithOutdatedAttributeOptionSpellcheckQuery,
        CreateCriteriaEvaluations $createProductsCriteriaEvaluations
    ) {
        $this->beConstructedWith(
            $getUpdatedProductIdsQuery,
            $getProductIdsWithOutdatedAttributeSpellcheckQuery,
            $getProductIdsWithUpdatedFamilyAttributesListQuery,
            $getProductIdsWithOutdatedAttributeOptionSpellcheckQuery,
            $createProductsCriteriaEvaluations
        );
    }

    public function it_creates_missing_criterion_evaluations_for_products_updated_since_a_given_date(
        GetUpdatedProductIdsQueryInterface $getUpdatedProductIdsQuery,
        CreateCriteriaEvaluations $createProductsCriteriaEvaluations
    ) {
        $updatedSince = new \DateTimeImmutable();
        $productIdsBatch1 = [new ProductId(42), new ProductId(123)];
        $productIdsBatch2 = [new ProductId(321)];

        $getUpdatedProductIdsQuery->since($updatedSince, 2)->willReturn(
            new \ArrayIterator([$productIdsBatch1, $productIdsBatch2])
        );

        $createProductsCriteriaEvaluations->createAll($productIdsBatch1)->shouldBeCalled();
        $createProductsCriteriaEvaluations->createAll($productIdsBatch2)->shouldBeCalled();

        $this->createForProductsUpdatedSince($updatedSince, 2);
    }

    public function it_creates_criterion_evaluations_for_products_impacted_by_updates_on_structure(
        $getProductIdsWithOutdatedAttributeSpellcheckQuery,
        $getProductIdsWithUpdatedFamilyAttributesListQuery,
        $getProductIdsWithOutdatedAttributeOptionSpellcheckQuery,
        $createProductsCriteriaEvaluations
    ) {
        $attributeSpellcheckCriterionCode = new CriterionCode(EvaluateAttributeSpelling::CRITERION_CODE);
        $attributeOptionSpellcheckCriterionCode = new CriterionCode(EvaluateAttributeOptionSpelling::CRITERION_CODE);
        $updatedSince = new \DateTimeImmutable();
        $productIdsBatch1 = [new ProductId(42), new ProductId(123)];
        $productIdsBatch2 = [new ProductId(321)];

        $getProductIdsWithOutdatedAttributeSpellcheckQuery->evaluatedSince($updatedSince, 2)->willReturn(
            new \ArrayIterator([$productIdsBatch1, $productIdsBatch2])
        );

        $createProductsCriteriaEvaluations->create([$attributeSpellcheckCriterionCode], $productIdsBatch1)->shouldBeCalled();
        $createProductsCriteriaEvaluations->create([$attributeSpellcheckCriterionCode], $productIdsBatch2)->shouldBeCalled();

        $productIdsBatch3 = [new ProductId(456)];
        $getProductIdsWithUpdatedFamilyAttributesListQuery->updatedSince($updatedSince, 2)->willReturn(
            new \ArrayIterator([$productIdsBatch3])
        );

        $createProductsCriteriaEvaluations->create([$attributeSpellcheckCriterionCode], $productIdsBatch3)->shouldBeCalled();

        $productIdsBatch4 = [new ProductId(789), new ProductId(987)];
        $getProductIdsWithOutdatedAttributeOptionSpellcheckQuery->evaluatedSince($updatedSince, 2)->willReturn(
            new \ArrayIterator([$productIdsBatch4])
        );

        $createProductsCriteriaEvaluations->create([$attributeOptionSpellcheckCriterionCode], $productIdsBatch4)->shouldBeCalled();

        $this->createForProductsImpactedByStructureUpdatedSince($updatedSince, 2);
    }
}
