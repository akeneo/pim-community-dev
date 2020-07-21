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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency\EvaluateAttributeOptionSpelling;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeOptionSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetProductIdsByAttributeOptionCodeQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\FilterProductIdsWithCriterionNotEvaluatedSinceQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductIdsWithOutdatedAttributeOptionSpellcheckQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAttributeOptionSpellcheckQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeOptionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;

final class GetProductIdsWithOutdatedAttributeOptionSpellcheckQuery implements GetProductIdsWithOutdatedAttributeOptionSpellcheckQueryInterface
{
    /** @var GetProductIdsByAttributeOptionCodeQueryInterface */
    private $getProductIdsByAttributeOptionCodesQuery;

    /** @var GetAttributeOptionSpellcheckQueryInterface */
    private $getAttributeOptionSpellcheckQuery;

    /** @var FilterProductIdsWithCriterionNotEvaluatedSinceQueryInterface */
    private $filterProductIdsWithCriterionNotEvaluatedSinceQuery;

    public function __construct(
        GetProductIdsByAttributeOptionCodeQueryInterface $getProductIdsByAttributeOptionCodesQuery,
        GetAttributeOptionSpellcheckQueryInterface $getAttributeOptionSpellcheckQuery,
        FilterProductIdsWithCriterionNotEvaluatedSinceQueryInterface $filterProductIdsWithCriterionNotEvaluatedSinceQuery
    ) {
        $this->getProductIdsByAttributeOptionCodesQuery = $getProductIdsByAttributeOptionCodesQuery;
        $this->getAttributeOptionSpellcheckQuery = $getAttributeOptionSpellcheckQuery;
        $this->filterProductIdsWithCriterionNotEvaluatedSinceQuery = $filterProductIdsWithCriterionNotEvaluatedSinceQuery;
    }

    public function evaluatedSince(\DateTimeImmutable $evaluatedSince, int $bulkSize): \Iterator
    {
        $productIdsBulk = [];
        /** @var AttributeOptionSpellcheck $attributeOptionSpellcheck */
        foreach ($this->getAttributeOptionSpellcheckQuery->evaluatedSince($evaluatedSince) as $attributeOptionSpellcheck) {
            foreach ($this->getProductIdsWithOutdatedAttributeOptionSpellcheckByAttributeOptionCode(
                $attributeOptionSpellcheck->getAttributeOptionCode(), $attributeOptionSpellcheck->getEvaluatedAt(), $bulkSize) as $productIds
            ) {
                $nbProductIdsToPick = max(0, $bulkSize - count($productIdsBulk));
                $productIdsBulk = array_merge($productIdsBulk, array_slice($productIds, 0, $nbProductIdsToPick));

                if (count($productIdsBulk) >= $bulkSize) {
                    yield $productIdsBulk;

                    $productIdsBulk = $nbProductIdsToPick < $bulkSize ? array_slice($productIds, $nbProductIdsToPick) : [];
                }
            }
        }

        if (!empty($productIdsBulk)) {
            yield $productIdsBulk;
        }
    }

    private function getProductIdsWithOutdatedAttributeOptionSpellcheckByAttributeOptionCode(
        AttributeOptionCode $attributeOptionCode,
        \DateTimeImmutable $evaluatedSince,
        int $bulkSize
    ): \Iterator {
        $productIdsBulk = [];
        foreach ($this->getProductIdsByAttributeOptionCodesQuery->execute($attributeOptionCode, $bulkSize) as $productIds) {
            $productIds = $this->filterProductIdsWithCriterionNotEvaluatedSinceQuery->execute(
                $productIds, $evaluatedSince, new CriterionCode(EvaluateAttributeOptionSpelling::CRITERION_CODE)
            );
            if (empty($productIds)) {
                continue;
            }

            $nbProductIdsToPick = max(0, $bulkSize - count($productIdsBulk));
            $productIdsBulk = array_merge($productIdsBulk, array_slice($productIds, 0, $nbProductIdsToPick));

            if (count($productIdsBulk) >= $bulkSize) {
                yield $productIdsBulk;

                $productIdsBulk = $nbProductIdsToPick < $bulkSize ? array_slice($productIds, $nbProductIdsToPick) : [];
            }
        }

        if (!empty($productIdsBulk)) {
            yield $productIdsBulk;
        }
    }
}
