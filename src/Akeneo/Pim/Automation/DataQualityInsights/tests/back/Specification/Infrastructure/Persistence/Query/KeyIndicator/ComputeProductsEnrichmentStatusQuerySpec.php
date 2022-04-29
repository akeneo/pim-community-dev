<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\KeyIndicator;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfNonRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionEvaluationResultStatusCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetEvaluationResultsByProductsAndCriterionQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ComputeProductsEnrichmentStatusQuerySpec extends ObjectBehavior
{
    public function let(
        GetLocalesByChannelQueryInterface $getLocalesByChannelQuery,
        GetEvaluationResultsByProductsAndCriterionQueryInterface $getEvaluationResultsByProductsAndCriterionQuery
    ) {
        $getLocalesByChannelQuery->getArray()->willReturn([
            'ecommerce' => ['en_US', 'fr_FR'],
            'mobile' => ['en_US'],
        ]);

        $this->beConstructedWith($getLocalesByChannelQuery, $getEvaluationResultsByProductsAndCriterionQuery);
    }

    public function it_computes_enrichment_status_for_a_list_of_products($getEvaluationResultsByProductsAndCriterionQuery)
    {
        $uuid42 = '54162e35-ff81-48f1-96d5-5febd3f00fd5';
        $uuid56 = 'df470d52-7723-4890-85a0-e79be625e2ed';
        $productIds = ProductUuidCollection::fromStrings([$uuid42, $uuid56]);

        $requiredAttributesResultDataProduct42 = [
            'total_number_of_attributes' => [
                'ecommerce' => [
                    'en_US' => 5,
                    'fr_FR' => 5,
                ],
                'mobile' => [
                    'en_US' => 2
                ]
            ],
            'number_of_improvable_attributes' => [
                'ecommerce' => [
                    'en_US' => 1,
                    'fr_FR' => 1,
                ],
                'mobile' => [
                    'en_US' => 0
                ]
            ],
        ];

        $nonRequiredAttributesResultDataProduct42 = [
            'total_number_of_attributes' => [
                'ecommerce' => [
                    'en_US' => 5,
                    'fr_FR' => 5,
                ],
                'mobile' => [
                    'en_US' => 8
                ]
            ],
            'number_of_improvable_attributes' => [
                'ecommerce' => [
                    'en_US' => 0,
                    'fr_FR' => 1,
                ],
                'mobile' => [
                    'en_US' => 5
                ]
            ],
        ];

        $requiredAttributesResultDataProduct56 = [
            'total_number_of_attributes' => [
                'ecommerce' => [
                    'en_US' => 2,
                    'fr_FR' => 2,
                ],
                'mobile' => [
                    'en_US' => 5,
                ]
            ],
            'number_of_improvable_attributes' => [
                'ecommerce' => [
                    'en_US' => 0,
                    'fr_FR' => 1,
                ],
                'mobile' => [
                    'en_US' => 2,
                ]
            ],
        ];

        $nonRequiredAttributesResultDataProduct56 = [
            'total_number_of_attributes' => [
                'ecommerce' => [
                    'en_US' => 8,
                    'fr_FR' => 8,
                ],
                'mobile' => [
                    'en_US' => 5,
                ]
            ],
            'number_of_improvable_attributes' => [
                'ecommerce' => [
                    'en_US' => 0,
                    'fr_FR' => 2,
                ],
                'mobile' => [
                    'en_US' => 0,
                ]
            ],
        ];

        $expectedResults = [
            $uuid42 => [
                'ecommerce' => [
                    'en_US' => true,
                    'fr_FR' => true,
                ],
                'mobile' => [
                    'en_US' => false
                ]
            ],
            $uuid56 => [
                'ecommerce' => [
                    'en_US' => true,
                    'fr_FR' => false,
                ],
                'mobile' => [
                    'en_US' => true
                ]
            ]
        ];

        $getEvaluationResultsByProductsAndCriterionQuery->execute(
            $productIds,
            new CriterionCode(EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE)
        )->willReturn([
            $uuid42 => new CriterionEvaluationResult(
                new ChannelLocaleRateCollection(),
                new CriterionEvaluationResultStatusCollection(),
                $requiredAttributesResultDataProduct42
            ),
            $uuid56 => new CriterionEvaluationResult(
                new ChannelLocaleRateCollection(),
                new CriterionEvaluationResultStatusCollection(),
                $requiredAttributesResultDataProduct56
            ),
        ]);

        $getEvaluationResultsByProductsAndCriterionQuery->execute(
            $productIds,
            new CriterionCode(EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE)
        )->willReturn([
            $uuid42 => new CriterionEvaluationResult(
                new ChannelLocaleRateCollection(),
                new CriterionEvaluationResultStatusCollection(),
                $nonRequiredAttributesResultDataProduct42
            ),
            $uuid56 => new CriterionEvaluationResult(
                new ChannelLocaleRateCollection(),
                new CriterionEvaluationResultStatusCollection(),
                $nonRequiredAttributesResultDataProduct56
            ),
        ]);

        $this->compute($productIds)->shouldReturn($expectedResults);
    }

    public function it_does_not_compute_products_without_evaluations($getEvaluationResultsByProductsAndCriterionQuery): void
    {
        $uuid = '54162e35-ff81-48f1-96d5-5febd3f00fd5';
        $productIds = ProductUuidCollection::fromString($uuid);

        $getEvaluationResultsByProductsAndCriterionQuery->execute(
            $productIds,
            new CriterionCode(EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE)
        )->willReturn([]);

        $getEvaluationResultsByProductsAndCriterionQuery->execute(
            $productIds,
            new CriterionCode(EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE)
        )->willReturn([]);

        $this->compute($productIds)->shouldReturn([
            $uuid => [
                'ecommerce' => [
                    'en_US' => null,
                    'fr_FR' => null,
                ],
                'mobile' => [
                    'en_US' => null
                ]
            ]
        ]);
    }

    public function it_computes_enrichment_status_for_products_with_only_required_attributes($getEvaluationResultsByProductsAndCriterionQuery): void
    {
        $uuid = '54162e35-ff81-48f1-96d5-5febd3f00fd5';
        $productIds = ProductUuidCollection::fromString($uuid);

        $requiredAttributesResultDataProduct = [
            'total_number_of_attributes' => [
                'ecommerce' => [
                    'en_US' => 10,
                    'fr_FR' => 10,
                ],
                'mobile' => [
                    'en_US' => 5
                ]
            ],
            'number_of_improvable_attributes' => [
                'ecommerce' => [
                    'en_US' => 1,
                    'fr_FR' => 4,
                ],
                'mobile' => [
                    'en_US' => 3
                ]
            ],
        ];

        $getEvaluationResultsByProductsAndCriterionQuery->execute(
            $productIds,
            new CriterionCode(EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE)
        )->willReturn([
            $uuid => new CriterionEvaluationResult(
                new ChannelLocaleRateCollection(),
                new CriterionEvaluationResultStatusCollection(),
                $requiredAttributesResultDataProduct
            ),
        ]);

        $getEvaluationResultsByProductsAndCriterionQuery->execute(
            $productIds,
            new CriterionCode(EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE)
        )->willReturn([
            $uuid => new CriterionEvaluationResult(
                new ChannelLocaleRateCollection(),
                new CriterionEvaluationResultStatusCollection(),
                []
            ),
        ]);

        $this->compute($productIds)->shouldReturn([
            $uuid => [
                'ecommerce' => [
                    'en_US' => true,
                    'fr_FR' => false,
                ],
                'mobile' => [
                    'en_US' => false
                ]
            ],
        ]);
    }

    public function it_computes_enrichment_status_for_products_without_required_attributes($getEvaluationResultsByProductsAndCriterionQuery): void
    {
        $uuid = '54162e35-ff81-48f1-96d5-5febd3f00fd5';
        $productIds = ProductUuidCollection::fromString($uuid);

        $nonRequiredAttributesResultDataProduct = [
            'total_number_of_attributes' => [
                'ecommerce' => [
                    'en_US' => 10,
                    'fr_FR' => 10,
                ],
                'mobile' => [
                    'en_US' => 5
                ]
            ],
            'number_of_improvable_attributes' => [
                'ecommerce' => [
                    'en_US' => 1,
                    'fr_FR' => 4,
                ],
                'mobile' => [
                    'en_US' => 3
                ]
            ],
        ];

        $getEvaluationResultsByProductsAndCriterionQuery->execute(
            $productIds,
            new CriterionCode(EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE)
        )->willReturn([
            $uuid => new CriterionEvaluationResult(
                new ChannelLocaleRateCollection(),
                new CriterionEvaluationResultStatusCollection(),
                []
            ),
        ]);

        $getEvaluationResultsByProductsAndCriterionQuery->execute(
            $productIds,
            new CriterionCode(EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE)
        )->willReturn([
            $uuid => new CriterionEvaluationResult(
                new ChannelLocaleRateCollection(),
                new CriterionEvaluationResultStatusCollection(),
                $nonRequiredAttributesResultDataProduct
            ),
        ]);

        $this->compute($productIds)->shouldReturn([
            $uuid => [
                'ecommerce' => [
                    'en_US' => true,
                    'fr_FR' => false,
                ],
                'mobile' => [
                    'en_US' => false
                ]
            ],
        ]);
    }
}
