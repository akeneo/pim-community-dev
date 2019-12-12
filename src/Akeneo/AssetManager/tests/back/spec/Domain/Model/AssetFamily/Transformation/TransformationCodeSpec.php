<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\TransformationCode;
use PhpSpec\ObjectBehavior;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class TransformationCodeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromString', ['code']);
    }

    function it_is_initializable()
    {
        $this->beAnInstanceOf(TransformationCode::class);
    }

    function it_can_be_constructed_with_alphanumerics_and_underscores()
    {
        $this->beConstructedThrough('fromString', ['MyCode_12']);
        $this->beAnInstanceOf(TransformationCode::class);
    }

    function it_throws_an_exception_if_string_code_contains_hyphen()
    {
        $this->beConstructedThrough('fromString', ['code-2']);
        $this->shouldThrow(new \LogicException('Transformation code may contain only letters, numbers and underscores. "code-2" given'))
            ->duringInstantiation();
    }

    function it_throws_an_exception_if_string_code_contains_space()
    {
        $this->beConstructedThrough('fromString', ['code 2']);
        $this->shouldThrow(new \LogicException('Transformation code may contain only letters, numbers and underscores. "code 2" given'))
            ->duringInstantiation();
    }

    function it_throws_an_exception_if_string_code_contains_illegal_characters()
    {
        $this->beConstructedThrough('fromString', ['code&2']);
        $this->shouldThrow(new \LogicException('Transformation code may contain only letters, numbers and underscores. "code&2" given'))
            ->duringInstantiation();
    }

    function it_can_be_compared_to_another_transformation_code()
    {
        $this->equals(TransformationCode::fromString('code'))->shouldReturn(true);
        $this->equals(TransformationCode::fromString('new_code'))->shouldReturn(false);
    }

    function it_can_be_normalized()
    {
        $this->normalize()->shouldReturn('code');
    }
}
