<?php

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation;

interface GetProductsEvaluationsDataByCriterionInterface
{
    public function execute(string $criterionCode, array $productIds): array;
}
