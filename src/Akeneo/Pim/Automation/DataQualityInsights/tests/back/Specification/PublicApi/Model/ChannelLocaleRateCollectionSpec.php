<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model;

use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\ValueObject\Rate;
use PhpSpec\ObjectBehavior;
use Webmozart\Assert\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ChannelLocaleRateCollectionSpec extends ObjectBehavior
{
    public function it_can_be_constructed_from_an_array_of_rates_as_integer()
    {
        $this->beConstructedThrough('fromArrayInt', [[
            'mobile' => [
                'en_US' => 87,
                'fr_FR' => 34,
            ],
            'print' => [
                'en_US' => 42,
            ],
        ]]);

        $expectedRates = [
            'mobile' => [
                'en_US' => 'B',
                'fr_FR' => 'E',
            ],
            'print' => [
                'en_US' => 'E',
            ],
        ];

        $this->toArrayLetter()->shouldBeLike($expectedRates);
    }
}
