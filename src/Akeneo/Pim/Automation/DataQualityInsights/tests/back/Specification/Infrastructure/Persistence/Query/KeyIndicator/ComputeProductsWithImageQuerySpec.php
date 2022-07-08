<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\KeyIndicator;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateImageEnrichment;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetEvaluationRatesByProductsAndCriterionQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
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
        $uuid13 = '54162e35-ff81-48f1-96d5-5febd3f00fd5';
        $uuid42 = 'df470d52-7723-4890-85a0-e79be625e2ed';
        $uuid99 = 'fef37e64-a963-47a9-b087-2cc67968f0a2';

        $productUuids = ProductUuidCollection::fromStrings([$uuid13, $uuid42, $uuid99]);
        $criterionCode = new CriterionCode(EvaluateImageEnrichment::CRITERION_CODE);

        $getEvaluationRatesByProductAndCriterionQuery->execute($productUuids, $criterionCode)->willReturn([
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

        $this->compute($productUuids)->shouldBeLike([
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
