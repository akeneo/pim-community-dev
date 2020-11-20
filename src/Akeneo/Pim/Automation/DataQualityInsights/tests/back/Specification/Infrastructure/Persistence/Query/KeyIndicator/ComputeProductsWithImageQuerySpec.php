<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\KeyIndicator;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateImageEnrichment;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetEvaluationRatesByProductsAndCriterionQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ComputeProductsWithImageQuerySpec extends ObjectBehavior
{
    public function let(GetEvaluationRatesByProductsAndCriterionQueryInterface $getEvaluationRatesByProductAndCriterionQuery)
    {
        $this->beConstructedWith($getEvaluationRatesByProductAndCriterionQuery);
    }

    public function it_computes_products_with_image_key_indicator($getEvaluationRatesByProductAndCriterionQuery)
    {
        $productIds = [new ProductId(13), new ProductId(42), new ProductId(999)];
        $criterionCode = new CriterionCode(EvaluateImageEnrichment::CRITERION_CODE);

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

        $this->compute($productIds)->shouldBeLike([
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
