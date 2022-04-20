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

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency\EvaluateAttributeOptionSpelling;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeOptionSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\SpellcheckResultByLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetProductIdsByAttributeOptionCodeQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\FilterProductIdsWithCriterionNotEvaluatedSinceQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAttributeOptionSpellcheckQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeOptionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use PhpSpec\ObjectBehavior;
use Webmozart\Assert\Assert;

final class GetProductIdsWithOutdatedAttributeOptionSpellcheckQuerySpec extends ObjectBehavior
{
    public function let(
        GetProductIdsByAttributeOptionCodeQueryInterface $getProductIdsByAttributeOptionCodesQuery,
        GetAttributeOptionSpellcheckQueryInterface $getAttributeOptionSpellcheckQuery,
        FilterProductIdsWithCriterionNotEvaluatedSinceQueryInterface $filterProductIdsWithCriterionNotEvaluatedSinceQuery,
        ProductEntityIdFactoryInterface $idFactory
    ) {
        $this->beConstructedWith(
            $getProductIdsByAttributeOptionCodesQuery,
            $getAttributeOptionSpellcheckQuery,
            $filterProductIdsWithCriterionNotEvaluatedSinceQuery,
            $idFactory
        );
    }

    public function it_retrieves_product_ids_with_outdated_attribute_option_spellcheck(
        $getProductIdsByAttributeOptionCodesQuery,
        $getAttributeOptionSpellcheckQuery,
        $filterProductIdsWithCriterionNotEvaluatedSinceQuery,
        $idFactory
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

        $productIdCollectionA = ProductIdCollection::fromStrings(['12', '34', '56']);
        $productIdCollectionB = ProductIdCollection::fromStrings(['78', '90', '99']);
        $productIdCollectionC = ProductIdCollection::fromStrings(['42', '43']);
        $productIdCollectionD = ProductIdCollection::fromStrings(['123']);

        $filteredProductIdCollectionA = ProductIdCollection::fromStrings(['12', '34']);
        $filteredProductIdCollectionB = ProductIdCollection::fromStrings([]);
        $filteredProductIdCollectionC = ProductIdCollection::fromStrings(['42', '43']);
        $filteredProductIdCollectionD = ProductIdCollection::fromStrings(['123']);

        $expectedProductIdCollectionA = ProductIdCollection::fromStrings(['12', '34', '42']);
        $expectedProductIdCollectionB = ProductIdCollection::fromStrings(['43', '123']);

        $idFactory->createCollection(['12', '34', '42'])->willReturn($expectedProductIdCollectionA);
        $idFactory->createCollection(['43', '123'])->willReturn($expectedProductIdCollectionB);

        $getProductIdsByAttributeOptionCodesQuery->execute($colorRedSpellcheck->getAttributeOptionCode(), $bulkSize)->willReturn(new \ArrayIterator([
            $productIdCollectionA,
            $productIdCollectionB,
            $productIdCollectionC
        ]));

        $filterProductIdsWithCriterionNotEvaluatedSinceQuery->execute(
            $productIdCollectionA, $colorRedSpellcheck->getEvaluatedAt(), new CriterionCode(EvaluateAttributeOptionSpelling::CRITERION_CODE)
        )->willReturn($filteredProductIdCollectionA);
        $filterProductIdsWithCriterionNotEvaluatedSinceQuery->execute(
            $productIdCollectionB, $colorRedSpellcheck->getEvaluatedAt(), new CriterionCode(EvaluateAttributeOptionSpelling::CRITERION_CODE)
        )->willReturn($filteredProductIdCollectionB);
        $filterProductIdsWithCriterionNotEvaluatedSinceQuery->execute(
            $productIdCollectionC, $colorRedSpellcheck->getEvaluatedAt(), new CriterionCode(EvaluateAttributeOptionSpelling::CRITERION_CODE)
        )->willReturn($filteredProductIdCollectionC);

        $getProductIdsByAttributeOptionCodesQuery->execute($materialWoodSpellcheck->getAttributeOptionCode(), $bulkSize)->willReturn(new \ArrayIterator([
            $productIdCollectionD
        ]));
        $filterProductIdsWithCriterionNotEvaluatedSinceQuery->execute(
            $productIdCollectionD, $materialWoodSpellcheck->getEvaluatedAt(), new CriterionCode(EvaluateAttributeOptionSpelling::CRITERION_CODE)
        )->willReturn($filteredProductIdCollectionD);

        $productIds = $this->evaluatedSince($evaluatedSince, $bulkSize);

        Assert::eq(iterator_to_array($productIds->getWrappedObject()), [
            $expectedProductIdCollectionA,
            $expectedProductIdCollectionB
        ]);
    }
}
