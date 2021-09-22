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

namespace Specification\Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Validation;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Validation\DecimalsAllowedValidation;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Validation\TableValidation;
use PhpSpec\ObjectBehavior;

class DecimalsAllowedValidationSpec extends ObjectBehavior
{
    function it_can_be_created_with_true()
    {
        $this->beConstructedThrough('fromValue', [true]);

        $this->shouldImplement(TableValidation::class);
        $this->shouldBeAnInstanceOf(DecimalsAllowedValidation::class);
        $this->getValue()->shouldReturn(true);
    }

    function it_can_be_created_with_false()
    {
        $this->beConstructedThrough('fromValue', [false]);

        $this->shouldImplement(TableValidation::class);
        $this->shouldBeAnInstanceOf(DecimalsAllowedValidation::class);
        $this->getValue()->shouldReturn(false);
    }

    function it_cannot_be_created_with_a_string()
    {
        $this->beConstructedThrough('fromValue', ['test']);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
