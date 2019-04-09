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

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeOptionCode;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributeOptionCodeSpec extends ObjectBehavior
{
    public function it_is_an_attribute_option_code(): void
    {
        $this->beConstructedWith('foo');
        $this->shouldBeAnInstanceOf(AttributeOptionCode::class);
    }

    public function it_throws_an_exception_when_attribute_option_code_is_empty(): void
    {
        $this->beConstructedWith('');
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_can_test_if_2_attribute_option_codes_are_equal(): void
    {
        $this->beConstructedWith('foo');

        $attributeCodeOption2 = new AttributeOptionCode('foo');
        $this->equals($attributeCodeOption2)->shouldReturn(true);

        $attributeOptionCode3 = new AttributeOptionCode('bar');
        $this->equals($attributeOptionCode3)->shouldReturn(false);
    }

    public function it_returns_the_attribute_option_code(): void
    {
        $this->beConstructedWith('foo');
        $this->__toString()->shouldReturn('foo');
    }
}
