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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Domain\Common\ValueObject;

use Akeneo\Pim\Automation\SuggestData\Domain\Common\ValueObject\AttributeCode;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributeCodeSpec extends ObjectBehavior
{
    public function it_is_a_attribute_code(): void
    {
        $this->beConstructedWith('foo');
        $this->shouldBeAnInstanceOf(AttributeCode::class);
    }

    public function it_throws_an_exception_when_attribute_code_is_empty(): void
    {
        $this->beConstructedWith('');
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_returns_the_attribute_code(): void
    {
        $this->beConstructedWith('foo');
        $this->__toString()->shouldReturn('foo');
    }
}
