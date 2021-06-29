<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Enrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\CalculateProductCompletenessInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetProductIdentifierFromProductIdQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdentifier;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculator;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodes;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CalculateProductCompletenessSpec extends ObjectBehavior
{
    public function let(
        GetProductIdentifierFromProductIdQueryInterface $getProductIdentifierFromProductIdQuery,
        CompletenessCalculator $completenessCalculator
    ) {
        $this->beConstructedWith($getProductIdentifierFromProductIdQuery, $completenessCalculator);
    }

    public function it_calculate_product_completeness()
    {
        $this->shouldImplement(CalculateProductCompletenessInterface::class);
    }

    public function it_evaluates_the_completeness_criterion(
        GetProductIdentifierFromProductIdQueryInterface $getProductIdentifierFromProductIdQuery,
        CompletenessCalculator $completenessCalculator
    ) {
        $productId = new ProductId(42);
        $productIdentifier = new ProductIdentifier('ziggy_mug');
        $getProductIdentifierFromProductIdQuery->execute($productId)->willReturn($productIdentifier);

        $completenessCalculator->fromProductIdentifier('ziggy_mug')->willReturn(new ProductCompletenessWithMissingAttributeCodesCollection(
            42, [
                new ProductCompletenessWithMissingAttributeCodes(
                    'ecommerce', 'en_US', 10, [
                        'name', 'description', 'weight', 'height'
                    ]
                ),
                new ProductCompletenessWithMissingAttributeCodes(
                    'ecommerce', 'fr_FR', 10, [
                        'name', 'description', 'weight', 'height', 'width', 'brand'
                    ]
                ),
                new ProductCompletenessWithMissingAttributeCodes(
                    'print', 'en_US', 12, [
                        'name', 'description', 'weight', 'height'
                    ]
                ),
                new ProductCompletenessWithMissingAttributeCodes(
                    'print', 'fr_FR', 10, [
                        'name', 'description', 'weight', 'height', 'width', 'brand', 'color'
                    ]
                ),
            ]
        ));

        $evaluationResult = $this->calculate($productId);

        $evaluationResult->getRates()->toArrayInt()->shouldBeLike([
            'ecommerce' => [
                'en_US' => 60,
                'fr_FR' => 40,
            ],
            'print' => [
                'en_US' => 66,
                'fr_FR' => 30,
            ],
        ]);

        $evaluationResult->getMissingAttributes()->toArray()->shouldBeLike([
            'ecommerce' => [
                'en_US' => ['name', 'description', 'weight', 'height'],
                'fr_FR' => ['name', 'description', 'weight', 'height', 'width', 'brand'],
            ],
            'print' => [
                'en_US' => ['name', 'description', 'weight', 'height'],
                'fr_FR' => ['name', 'description', 'weight', 'height', 'width', 'brand', 'color'],
            ],
        ]);
    }
}
