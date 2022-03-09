<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model;

use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\ProductModelScore;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductModelScoreCollectionSpec extends ObjectBehavior
{
    public function it_can_be_constructed_and_returns_product_model_score()
    {
        $expectedProductModelScore = new ProductModelScore('A', 95);

        $productModelScores = [
            'ecommerce' => [
                'fr_FR' => $expectedProductModelScore,
                'en_US' => new ProductModelScore('B', 70),
            ],
            'print' => [
                'fr_FR' => new ProductModelScore('B', 70),
                'en_US' => new ProductModelScore('A', 95),
            ],
        ];

        $this->beConstructedWith($productModelScores);
        $this->getProductModelScoreByChannelAndLocale('ecommerce', 'fr_FR')->shouldReturn($expectedProductModelScore);
        $this->getProductModelScoreByChannelAndLocale('ecommerce', 'es_ES')->shouldReturn(null);
        $this->getProductModelScoreByChannelAndLocale('mobile', 'fr_FR')->shouldReturn(null);
    }
}
