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

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\IsRequiredForCompleteness;
use PhpSpec\ObjectBehavior;

final class IsRequiredForCompletenessSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedThrough('fromBoolean', [true]);
        $this->shouldHaveType(IsRequiredForCompleteness::class);
    }

    function it_is_required_for_completeness()
    {
        $this->beConstructedThrough('fromBoolean', [true]);
        $this->asBoolean()->shouldReturn(true);
    }

    function it_is_not_required_for_completeness()
    {
        $this->beConstructedThrough('fromBoolean', [false]);
        $this->asBoolean()->shouldReturn(false);
    }
}
