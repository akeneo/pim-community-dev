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
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\MarkCriteriaToEvaluateInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductIdsWithOutdatedAttributeOptionSpellcheckQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductIdsWithOutdatedAttributeSpellcheckQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductIdsWithUpdatedFamilyAttributesListQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use PhpSpec\ObjectBehavior;

final class PimEnterpriseMarkCriteriaToEvaluateSpec extends ObjectBehavior
{
    public function let(
        MarkCriteriaToEvaluateInterface $markCriteriaToEvaluate,
        GetProductIdsWithOutdatedAttributeSpellcheckQueryInterface $getProductIdsWithOutdatedAttributeSpellcheckQuery,
        GetProductIdsWithUpdatedFamilyAttributesListQueryInterface $getProductIdsWithUpdatedFamilyAttributesListQuery,
        GetProductIdsWithOutdatedAttributeOptionSpellcheckQueryInterface $getProductIdsWithOutdatedAttributeOptionSpellcheckQuery,
        CreateCriteriaEvaluations $createProductsCriteriaEvaluations
    ) {
        $this->beConstructedWith(
            $markCriteriaToEvaluate,
            $getProductIdsWithOutdatedAttributeSpellcheckQuery,
            $getProductIdsWithUpdatedFamilyAttributesListQuery,
            $getProductIdsWithOutdatedAttributeOptionSpellcheckQuery,
            $createProductsCriteriaEvaluations
        );
    }

    public function it_marks_criteria_to_evaluate_for_updates_since_a_given_date(
        $markCriteriaToEvaluate,
        $getProductIdsWithOutdatedAttributeSpellcheckQuery,
        $getProductIdsWithUpdatedFamilyAttributesListQuery,
        $getProductIdsWithOutdatedAttributeOptionSpellcheckQuery,
        $createProductsCriteriaEvaluations
    ) {
        $updatedSince = new \DateTimeImmutable();

        $markCriteriaToEvaluate->forUpdatesSince($updatedSince, 2)->shouldBeCalled();

        $attributeSpellcheckCriterionCode = new CriterionCode(EvaluateAttributeSpelling::CRITERION_CODE);
        $attributeOptionSpellcheckCriterionCode = new CriterionCode(EvaluateAttributeOptionSpelling::CRITERION_CODE);
        $productIdsBatch1 = [new ProductId(874), new ProductId(9786)];
        $productIdsBatch2 = [new ProductId(1265)];

        $getProductIdsWithOutdatedAttributeSpellcheckQuery->evaluatedSince($updatedSince, 2)->willReturn(
            new \ArrayIterator([$productIdsBatch1, $productIdsBatch2])
        );

        $createProductsCriteriaEvaluations->create([$attributeSpellcheckCriterionCode], $productIdsBatch1)->shouldBeCalled();
        $createProductsCriteriaEvaluations->create([$attributeSpellcheckCriterionCode], $productIdsBatch2)->shouldBeCalled();

        $productIdsBatch3 = [new ProductId(3964)];
        $getProductIdsWithUpdatedFamilyAttributesListQuery->updatedSince($updatedSince, 2)->willReturn(
            new \ArrayIterator([$productIdsBatch3])
        );

        $createProductsCriteriaEvaluations->create([$attributeSpellcheckCriterionCode], $productIdsBatch3)->shouldBeCalled();

        $productIdsBatch4 = [new ProductId(1789), new ProductId(1987)];
        $getProductIdsWithOutdatedAttributeOptionSpellcheckQuery->evaluatedSince($updatedSince, 2)->willReturn(
            new \ArrayIterator([$productIdsBatch4])
        );

        $createProductsCriteriaEvaluations->create([$attributeOptionSpellcheckCriterionCode], $productIdsBatch4)->shouldBeCalled();

        $this->forUpdatesSince($updatedSince, 2);
    }
}
