<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\API\Command\UserIntent;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\PriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetPriceCollectionValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetPriceCollectionValueSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            'msrp',
            'ecommerce',
            'en_US',
            [
                new PriceValue(20, "EUR"),
                new PriceValue(50, "USD"),
            ]
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SetPriceCollectionValue::class);
        $this->shouldImplement(ValueUserIntent::class);
    }

    function it_returns_the_attribute_code()
    {
        $this->attributeCode()->shouldReturn('msrp');
    }

    function it_returns_the_locale_code()
    {
        $this->localeCode()->shouldReturn('en_US');
    }

    function it_returns_the_channel_code()
    {
        $this->channelCode()->shouldReturn('ecommerce');
    }

    function it_returns_the_price_values()
    {
        $priceValues = [
            new PriceValue(20, "EUR"),
            new PriceValue(50, "USD"),
        ];

        $this->beConstructedWith(
            'msrp',
            'ecommerce',
            'en_US',
            $priceValues
        );

        $this->priceValues()->shouldReturn($priceValues);
    }

    function it_cannot_be_instantiated_with_other_values_than_price_values()
    {
        $this->beConstructedWith(
            'msrp',
            'ecommerce',
            'en_US',
            [
                'test'
            ]
        );

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
