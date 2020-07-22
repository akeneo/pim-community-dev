<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use PhpSpec\ObjectBehavior;

class AttributeOptionCodeSpec extends ObjectBehavior
{
    public function it_can_be_converted_to_string()
    {
        $this->beConstructedWith(new AttributeCode('color'), 'blue');

        $this->__toString()->shouldReturn('blue');
    }

    public function it_throws_an_exception_if_the_option_code_is_empty()
    {
        $this->beConstructedWith(new AttributeCode('color'), '');
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
