<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\API\Command\UserIntent;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearPriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ClearPriceValueSpec extends ObjectBehavior
{
    function let(): void
    {
        $this->beConstructedWith('name', 'ecommerce', 'en_US', 'EUR');
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(ClearPriceValue::class);
        $this->shouldImplement(ValueUserIntent::class);
    }

    function it_returns_the_attribute_code(): void
    {
        $this->attributeCode()->shouldReturn('name');
    }

    function it_returns_the_locale_code(): void
    {
        $this->localeCode()->shouldReturn('en_US');
    }

    function it_returns_the_channel_code(): void
    {
        $this->channelCode()->shouldReturn('ecommerce');
    }

    function it_returns_the_currency_code(): void
    {
        $this->currencyCode()->shouldReturn('EUR');
    }
}
