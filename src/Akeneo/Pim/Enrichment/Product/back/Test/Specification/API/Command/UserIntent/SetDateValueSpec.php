<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\API\Command\UserIntent;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetDateValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetDateValueSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('name', 'ecommerce', 'en_US', new \DateTime("2022-03-04T09:35:24+00:00"));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SetDateValue::class);
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
        $this->value()->shouldBeLike(new \DateTime("2022-03-04T09:35:24+00:00"));
    }
}
