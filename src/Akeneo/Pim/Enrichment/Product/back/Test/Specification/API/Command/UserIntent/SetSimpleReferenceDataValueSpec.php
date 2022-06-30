<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\API\Command\UserIntent;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleReferenceDataValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetSimpleReferenceDataValueSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('attribute_name', null, null, 'Akeneo');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SetSimpleReferenceDataValue::class);
        $this->shouldImplement(ValueUserIntent::class);
    }

    function it_returns_the_attribute_code()
    {
        $this->attributeCode()->shouldReturn('attribute_name');
    }

    function it_returns_the_locale_code()
    {
        $this->localeCode()->shouldReturn(null);
    }

    function it_returns_the_channel_code()
    {
        $this->channelCode()->shouldReturn(null);
    }

    function it_returns_the_value()
    {
        $this->value()->shouldReturn('Akeneo');
    }

    function it_cannot_be_instantiated_with_an_empty_value()
    {
        $this->beConstructedWith('attribute_name', null, null, '');
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
