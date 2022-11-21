<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\API\Command\UserIntent;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetNumberValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetNumberValueSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('name', 'ecommerce', 'en_US', '10');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SetNumberValue::class);
        $this->shouldImplement(ValueUserIntent::class);
    }

    function it_returns_the_attribute_code()
    {
        $this->attributeCode()->shouldReturn('name');
    }

    function it_returns_the_locale_code()
    {
        $this->localeCode()->shouldReturn('en_US');
    }

    function it_returns_the_channel_code()
    {
        $this->channelCode()->shouldReturn('ecommerce');
    }

    function it_returns_the_value()
    {
        $this->value()->shouldReturn('10');
    }

    function it_accepts_int_string_as_value()
    {
        $this->beConstructedWith('name_string_int', 'ecommerce', 'en_US', '33');
        $this->value()->shouldReturn('33');
    }

    function it_accepts_float_string_as_value()
    {
        $this->beConstructedWith('name_string_float', 'ecommerce', 'en_US', '33.33');
        $this->value()->shouldReturn('33.33');
    }
}
