<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class FamilyCodeSpec extends ObjectBehavior
{
    public function it_is_a_family_code(): void
    {
        $this->beConstructedWith('foo');
        $this->shouldBeAnInstanceOf(FamilyCode::class);
    }

    public function it_throws_an_exception_when_family_code_is_empty(): void
    {
        $this->beConstructedWith('');
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_returns_the_family_code(): void
    {
        $this->beConstructedWith('foo');
        $this->__toString()->shouldReturn('foo');
    }
}
