<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model;

use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScore;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class QualityScoreCollectionSpec extends ObjectBehavior
{
    public function it_can_be_constructed_and_returns_quality_score()
    {
        $expectedQualityScore = new QualityScore('A', 95);

        $qualityScores = [
            'ecommerce' => [
                'fr_FR' => $expectedQualityScore,
                'en_US' => new QualityScore('B', 70),
            ],
            'print' => [
                'fr_FR' => new QualityScore('B', 70),
                'en_US' => new QualityScore('A', 95),
            ],
        ];

        $this->beConstructedWith($qualityScores);
        $this->getQualityScoreByChannelAndLocale('ecommerce', 'fr_FR')->shouldReturn($expectedQualityScore);
        $this->getQualityScoreByChannelAndLocale('ecommerce', 'es_ES')->shouldReturn(null);
        $this->getQualityScoreByChannelAndLocale('mobile', 'fr_FR')->shouldReturn(null);
    }
}
