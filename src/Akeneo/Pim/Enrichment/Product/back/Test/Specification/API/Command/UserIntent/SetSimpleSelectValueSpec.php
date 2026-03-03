<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\API\Command\UserIntent;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetSimpleSelectValueSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('size', 'ecommerce', 'en_US', 'M');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SetSimpleSelectValue::class);
        $this->shouldImplement(ValueUserIntent::class);
    }

    function it_returns_the_attribute_code()
    {
        $this->attributeCode()->shouldReturn('size');
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
        $this->value()->shouldReturn('M');
    }

    function it_is_not_initializable_with_an_empty_value()
    {
        $this->beConstructedWith('name', 'ecommerce', 'en_US', '');
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
