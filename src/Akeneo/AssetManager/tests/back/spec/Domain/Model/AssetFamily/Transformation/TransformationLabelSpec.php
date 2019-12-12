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

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\TransformationLabel;
use PhpSpec\ObjectBehavior;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class TransformationLabelSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromString', ['my label']);
    }

    function it_is_initializable()
    {
        $this->beAnInstanceOf(TransformationLabel::class);
    }

    function it_throws_an_exception_if_string_label_is_empty()
    {
        $this->beConstructedThrough('fromString', ['']);
        $this->shouldThrow(\InvalidArgumentException::class)
            ->duringInstantiation();
    }

    function it_can_be_compared_to_another_transformation_code()
    {
        $this->equals(TransformationLabel::fromString('my label'))->shouldReturn(true);
        $this->equals(TransformationLabel::fromString('my label2'))->shouldReturn(false);
        $this->equals(TransformationLabel::fromString('my label '))->shouldReturn(false);
        $this->equals(TransformationLabel::fromString('My Label'))->shouldReturn(false);
    }

    function it_can_be_normalized()
    {
        $this->normalize()->shouldReturn('my label');
    }

    function it_can_be_stringified()
    {
        $this->normalize()->shouldReturn('my label');
    }
}
