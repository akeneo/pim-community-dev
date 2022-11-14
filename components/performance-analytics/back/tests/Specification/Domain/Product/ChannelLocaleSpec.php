<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\PerformanceAnalytics\Domain\Product;

use Akeneo\PerformanceAnalytics\Domain\Product\ChannelLocale;
use PhpSpec\ObjectBehavior;

class ChannelLocaleSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('fromChannelAndLocale', ['ecommerce', 'en_US']);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ChannelLocale::class);
    }

    public function it_normalizes_a_channel_locale()
    {
        $this->normalize()->shouldReturn([
            'channel_code' => 'ecommerce',
            'locale_code' => 'en_US',
        ]);
    }

    public function it_cannot_create_an_empty_channel_code()
    {
        $this->beConstructedThrough('fromChannelAndLocale', ['', 'en_US']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_cannot_create_an_empty_locale_code()
    {
        $this->beConstructedThrough('fromChannelAndLocale', ['ecommerce', '']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
