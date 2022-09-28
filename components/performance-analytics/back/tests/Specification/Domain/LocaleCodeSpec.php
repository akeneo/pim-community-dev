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

use Akeneo\PerformanceAnalytics\Domain\LocaleCode;
use PhpSpec\ObjectBehavior;

final class LocaleCodeSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('fromString', ['en_US']);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(LocaleCode::class);
    }

    public function it_returns_a_locale_code_as_string()
    {
        $this->toString()->shouldReturn('en_US');
    }

    public function it_cannot_create_an_empty_locale_code()
    {
        $this->beConstructedThrough('fromString', ['']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
