<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Enrichment\Product\Api\Command\UserIntent;

use Akeneo\Pim\Enrichment\Product\Api\Command\UserIntent\SetNumberValue;
use Akeneo\Pim\Enrichment\Product\Api\Command\UserIntent\ValueUserIntent;
use PhpSpec\ObjectBehavior;

class SetNumberValueSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('name', 'en_US', 'ecommerce', 10);
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
        $this->value()->shouldReturn(10);
    }
}
