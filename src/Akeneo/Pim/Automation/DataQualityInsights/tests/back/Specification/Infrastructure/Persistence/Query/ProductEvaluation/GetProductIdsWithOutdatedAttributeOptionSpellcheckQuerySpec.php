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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency\EvaluateAttributeOptionSpelling;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeOptionSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\SpellcheckResultByLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetProductIdsByAttributeOptionCodeQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\FilterProductIdsWithCriterionNotEvaluatedSinceQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAttributeOptionSpellcheckQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeOptionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use PhpSpec\ObjectBehavior;
use Webmozart\Assert\Assert;

final class GetProductIdsWithOutdatedAttributeOptionSpellcheckQuerySpec extends ObjectBehavior
{
    public function let(
        GetProductIdsByAttributeOptionCodeQueryInterface $getProductIdsByAttributeOptionCodesQuery,
        GetAttributeOptionSpellcheckQueryInterface $getAttributeOptionSpellcheckQuery,
        FilterProductIdsWithCriterionNotEvaluatedSinceQueryInterface $filterProductIdsWithCriterionNotEvaluatedSinceQuery
    ) {
        $this->beConstructedWith($getProductIdsByAttributeOptionCodesQuery, $getAttributeOptionSpellcheckQuery, $filterProductIdsWithCriterionNotEvaluatedSinceQuery);
    }

    public function it_retrieves_product_ids_with_outdated_attribute_option_spellcheck(
        $getProductIdsByAttributeOptionCodesQuery,
        $getAttributeOptionSpellcheckQuery,
        $filterProductIdsWithCriterionNotEvaluatedSinceQuery
    ) {
        $bulkSize = 3;
        $evaluatedSince = new \DateTimeImmutable('2020-06-12 15:43:31');

        $colorRedSpellcheck = new AttributeOptionSpellcheck(
            new AttributeOptionCode(new AttributeCode('color'), 'red'),
            $evaluatedSince->modify('+3 second'),
            new SpellcheckResultByLocaleCollection()
        );
        $materialWoodSpellcheck = new AttributeOptionSpellcheck(
            new AttributeOptionCode(new AttributeCode('material'), 'wood'),
            $evaluatedSince->modify('+1 minute'),
            new SpellcheckResultByLocaleCollection()
        );

        $getAttributeOptionSpellcheckQuery->evaluatedSince($evaluatedSince)->willReturn(new \ArrayIterator([
            $colorRedSpellcheck,
            $materialWoodSpellcheck
        ]));

        $productId12 = new ProductId(12);
        $productId34 = new ProductId(34);
        $productId56 = new ProductId(56);
        $productId78 = new ProductId(78);
        $productId90 = new ProductId(90);
        $productId99 = new ProductId(99);
        $productId42 = new ProductId(42);
        $productId43 = new ProductId(43);

        $getProductIdsByAttributeOptionCodesQuery->execute($colorRedSpellcheck->getAttributeOptionCode(), $bulkSize)->willReturn(new \ArrayIterator([
            [$productId12, $productId34, $productId56],
            [$productId78, $productId90, $productId99],
            [$productId42, $productId43]
        ]));

        $filterProductIdsWithCriterionNotEvaluatedSinceQuery->execute(
            [$productId12, $productId34, $productId56], $colorRedSpellcheck->getEvaluatedAt(), new CriterionCode(EvaluateAttributeOptionSpelling::CRITERION_CODE)
        )->willReturn([$productId12, $productId34]);
        $filterProductIdsWithCriterionNotEvaluatedSinceQuery->execute(
            [$productId78, $productId90, $productId99], $colorRedSpellcheck->getEvaluatedAt(), new CriterionCode(EvaluateAttributeOptionSpelling::CRITERION_CODE)
        )->willReturn([]);
        $filterProductIdsWithCriterionNotEvaluatedSinceQuery->execute(
            [$productId42, $productId43], $colorRedSpellcheck->getEvaluatedAt(), new CriterionCode(EvaluateAttributeOptionSpelling::CRITERION_CODE)
        )->willReturn([$productId42, $productId43]);

        $productId123 = new ProductId(123);
        $getProductIdsByAttributeOptionCodesQuery->execute($materialWoodSpellcheck->getAttributeOptionCode(), $bulkSize)->willReturn(new \ArrayIterator([
            [$productId123]
        ]));
        $filterProductIdsWithCriterionNotEvaluatedSinceQuery->execute(
            [$productId123], $materialWoodSpellcheck->getEvaluatedAt(), new CriterionCode(EvaluateAttributeOptionSpelling::CRITERION_CODE)
        )->willReturn([$productId123]);

        $productIds = $this->evaluatedSince($evaluatedSince, $bulkSize);

        Assert::eq(iterator_to_array($productIds->getWrappedObject()), [
            [$productId12, $productId34, $productId42],
            [$productId43, $productId123]
        ]);
    }
}
