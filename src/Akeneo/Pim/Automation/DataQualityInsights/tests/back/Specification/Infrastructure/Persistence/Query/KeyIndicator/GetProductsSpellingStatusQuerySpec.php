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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\KeyIndicator;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency\EvaluateSpelling;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetEvaluationRatesByProductsAndCriterionQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use PhpSpec\ObjectBehavior;

final class GetProductsSpellingStatusQuerySpec extends ObjectBehavior
{
    public function let(GetEvaluationRatesByProductsAndCriterionQueryInterface $getEvaluationRatesByProductAndCriterionQuery)
    {
        $this->beConstructedWith($getEvaluationRatesByProductAndCriterionQuery);
    }

    public function it_gives_products_with_spelling_status_key_indicator($getEvaluationRatesByProductAndCriterionQuery)
    {
        $productIds = [new ProductId(13), new ProductId(42), new ProductId(999)];
        $criterionCode = new CriterionCode(EvaluateSpelling::CRITERION_CODE);

        $getEvaluationRatesByProductAndCriterionQuery->toArrayInt($productIds, $criterionCode)->willReturn([
            13 => [
                'ecommerce' => [
                    'en_US' => 100,
                ],
                'mobile' => [
                    'en_US' => 0,
                ],
            ],
            42 => [
                'ecommerce' => [
                    'en_US' => 0,
                    'fr_FR' => 100,
                ],
            ],
        ]);

        $this->execute($productIds)->shouldBeLike([
            13 => [
                'ecommerce' => [
                    'en_US' => true,
                ],
                'mobile' => [
                    'en_US' => false,
                ],
            ],
            42 => [
                'ecommerce' => [
                    'en_US' => false,
                    'fr_FR' => true,
                ],
            ],
        ]);
    }
}
