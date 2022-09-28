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

namespace Specification\Akeneo\PerformanceAnalytics\Domain;

use Akeneo\PerformanceAnalytics\Domain\ChannelCode;
use PhpSpec\ObjectBehavior;

final class ChannelCodeSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('fromString', ['mobile']);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ChannelCode::class);
    }

    public function it_returns_a_channel_code_as_string()
    {
        $this->toString()->shouldReturn('mobile');
    }

    public function it_cannot_create_an_empty_channel_code()
    {
        $this->beConstructedThrough('fromString', ['']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
