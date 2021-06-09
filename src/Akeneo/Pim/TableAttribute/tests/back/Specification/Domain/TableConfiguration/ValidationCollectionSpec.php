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

namespace Specification\Akeneo\Pim\TableAttribute\Domain\TableConfiguration;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValidationCollection;
use PhpSpec\ObjectBehavior;

class ValidationCollectionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedThrough('fromNormalized', [['max_length' => 255]]);
        $this->shouldHaveType(ValidationCollection::class);
    }

    function it_throws_an_exception_when_there_are_no_keys()
    {
        $this->beConstructedThrough('fromNormalized', [['something']]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_throws_an_exception_when_keys_are_numbers()
    {
        $this->beConstructedThrough('fromNormalized', [[12 => 'something']]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_normalizes_itself()
    {
        $this->beConstructedThrough('fromNormalized', [['max_length' => 255]]);

        $this->normalize()->shouldReturn(['max_length' => 255]);
    }
}
