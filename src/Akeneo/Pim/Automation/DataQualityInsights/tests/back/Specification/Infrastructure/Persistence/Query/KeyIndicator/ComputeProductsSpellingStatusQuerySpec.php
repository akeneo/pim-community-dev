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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

final class ComputeProductsSpellingStatusQuerySpec extends ObjectBehavior
{
    public function let(GetEvaluationRatesByProductsAndCriterionQueryInterface $getEvaluationRatesByProductAndCriterionQuery)
    {
        $this->beConstructedWith($getEvaluationRatesByProductAndCriterionQuery);
    }

    public function it_computes_products_with_spelling_status_key_indicator($getEvaluationRatesByProductAndCriterionQuery)
    {
        $uuid13 = Uuid::uuid4()->toString();
        $uuid42 = Uuid::uuid4()->toString();
        $uuid99 = Uuid::uuid4()->toString();
        $productIdCollection = ProductUuidCollection::fromStrings([$uuid13, $uuid42, $uuid99]);
        $criterionCode = new CriterionCode(EvaluateSpelling::CRITERION_CODE);

        $getEvaluationRatesByProductAndCriterionQuery->execute($productIdCollection, $criterionCode)->willReturn([
            $uuid13 => [
                'ecommerce' => [
                    'en_US' => 100,
                ],
                'mobile' => [
                    'en_US' => 0,
                ],
            ],
            $uuid42 => [
                'ecommerce' => [
                    'en_US' => 0,
                    'fr_FR' => 100,
                ],
            ],
        ]);

        $this->compute($productIdCollection)->shouldBeLike([
            $uuid13 => [
                'ecommerce' => [
                    'en_US' => true,
                ],
                'mobile' => [
                    'en_US' => false,
                ],
            ],
            $uuid42 => [
                'ecommerce' => [
                    'en_US' => false,
                    'fr_FR' => true,
                ],
            ],
        ]);
    }
}
