<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model;

use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\ProductScore;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductScoreCollectionSpec extends ObjectBehavior
{
    public function it_can_be_constructed_and_returns_product_score()
    {
        $expectedProductScore = new ProductScore('A', 95);

        $productScores = [
            'ecommerce' => [
                'fr_FR' => $expectedProductScore,
                'en_US' => new ProductScore('B', 70),
            ],
            'print' => [
                'fr_FR' => new ProductScore('B', 70),
                'en_US' => new ProductScore('A', 95),
            ],
        ];

        $this->beConstructedWith($productScores);
        $this->getProductScoreByChannelAndLocale('ecommerce', 'fr_FR')->shouldReturn($expectedProductScore);
        $this->getProductScoreByChannelAndLocale('ecommerce', 'es_ES')->shouldReturn(null);
        $this->getProductScoreByChannelAndLocale('mobile', 'fr_FR')->shouldReturn(null);
    }
}
