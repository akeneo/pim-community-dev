<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\API\Command\UserIntent;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiReferenceDataValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetMultiReferenceDataValueSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('attribute_ref_data', null, null, ['Akeneo', 'Ziggy']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SetMultiReferenceDataValue::class);
        $this->shouldImplement(ValueUserIntent::class);
    }

    function it_returns_the_attribute_code()
    {
        $this->attributeCode()->shouldReturn('attribute_ref_data');
    }

    function it_returns_the_locale_code()
    {
        $this->localeCode()->shouldReturn(null);
    }

    function it_returns_the_channel_code()
    {
        $this->channelCode()->shouldReturn(null);
    }

    function it_returns_the_values()
    {
        $this->values()->shouldReturn(['Akeneo', 'Ziggy']);
    }

    function it_can_only_be_instantiated_with_string_values()
    {
        $this->beConstructedWith('attribute_ref_data', null, null, ['test', 12, false]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_be_instantiated_with_empty_values()
    {
        $this->beConstructedWith('attribute_ref_data', null, null, []);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_be_instantiated_if_one_of_the_values_is_empty()
    {
        $this->beConstructedWith('attribute_ref_data', null, null, ['a', '', 'b']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
