<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\API\Command\UserIntent;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\PriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetPriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetPriceValueSpec extends ObjectBehavior
{
    function let()
    {
        $priceValue = new PriceValue('100', 'USD');
        $this->beConstructedWith('net_price', 'ecommerce', 'en_US', $priceValue);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SetPriceValue::class);
        $this->shouldImplement(ValueUserIntent::class);
    }

    function it_returns_the_attribute_code()
    {
        $this->attributeCode()->shouldReturn('net_price');
    }

    function it_returns_the_locale_code()
    {
        $this->localeCode()->shouldReturn('en_US');
    }

    function it_returns_the_channel_code()
    {
        $this->channelCode()->shouldReturn('ecommerce');
    }

    function it_returns_the_price_value()
    {
        $priceValue = new PriceValue('100', 'USD');
        $this->priceValue()->shouldBeLike($priceValue);
    }
}
