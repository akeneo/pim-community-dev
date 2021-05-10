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

namespace Specification\Akeneo\Pim\TableAttribute\Domain\Config;

use PhpSpec\ObjectBehavior;

class LabelCollectionSpec extends ObjectBehavior
{
    function it_throws_an_exception_when_an_array_key_is_not_string()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'en_US' => '',
            1 => '',
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_throws_an_exception_when_an_array_key_is_an_empty_string()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'en_US' => '',
            '' => '',
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_throws_an_exception_when_a_value_is_not_a_string()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'en_US' => '',
            'fr_FR' => 12,
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_returns_the_labels()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'en_US' => '',
            'fr_FR' => '',
        ]]);
        $this->labels()->shouldBe([
            'en_US' => '',
            'fr_FR' => '',
        ]);
    }
}
