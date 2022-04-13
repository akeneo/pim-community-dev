<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\API\Command\UserIntent;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMeasurementValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetMeasurementValueSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('power', 'ecommerce', 'en_US', '100', 'KILOWATT');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SetMeasurementValue::class);
        $this->shouldImplement(ValueUserIntent::class);
    }

    function it_returns_the_attribute_code()
    {
        $this->attributeCode()->shouldReturn('power');
    }

    function it_returns_the_locale_code()
    {
        $this->localeCode()->shouldReturn('en_US');
    }

    function it_returns_the_channel_code()
    {
        $this->channelCode()->shouldReturn('ecommerce');
    }

    function it_returns_the_amount()
    {
        $this->amount()->shouldReturn('100');
    }

    function it_returns_the_unit()
    {
        $this->unit()->shouldReturn('KILOWATT');
    }
}
