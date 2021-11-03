<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\SelectOptionCode;
use PhpSpec\ObjectBehavior;

class SelectOptionCodeSpec extends ObjectBehavior
{
    function it_can_be_instantiated()
    {
        $this->beConstructedThrough('fromString', ['salt']);
        $this->shouldBeAnInstanceOf(SelectOptionCode::class);
    }

    function it_throws_an_error_when_code_is_empty()
    {
        $this->beConstructedThrough('fromString', ['']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_can_be_displayed_as_a_string()
    {
        $this->beConstructedThrough('fromString', ['salt']);
        $this->asString()->shouldBe('salt');
    }
}
