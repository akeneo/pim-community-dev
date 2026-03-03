<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\API\Command\UserIntent;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetMultiSelectValueSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('tags', 'ecommerce', 'en_US', ['uno', 'dos', 'tres']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SetMultiSelectValue::class);
        $this->shouldImplement(ValueUserIntent::class);
    }

    function it_returns_the_attribute_code()
    {
        $this->attributeCode()->shouldReturn('tags');
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
        $this->values()->shouldReturn(['uno', 'dos', 'tres']);
    }

    function it_can_only_be_instanced_with_string_values()
    {
        $this->beConstructedWith('name', 'ecommerce', 'en_US', ['test', 12, false]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_be_instanced_with_empty_values_array()
    {
        $this->beConstructedWith('name', 'ecommerce', 'en_US', []);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_be_instanced_if_one_of_the_values_is_empty()
    {
        $this->beConstructedWith('name', 'ecommerce', 'en_US', ['a', '', 'b']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
