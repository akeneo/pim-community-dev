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

namespace spec\Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ResizeOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation;
use PhpSpec\ObjectBehavior;

class ResizeOperationSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('create', [[
            'width' => 200,
            'height' => 100,
        ]]);
    }

    function it_is_an_operation()
    {
        $this->beAnInstanceOf(Operation::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ResizeOperation::class);
    }

    function it_returns_its_type()
    {
        $this::getType()->shouldBe('resize');
    }

    function it_returns_width_and_height()
    {
        $this->getWidth()->shouldBe(200);
        $this->getHeight()->shouldBe(100);
    }

    function it_can_not_be_constructed_without_width()
    {
        $this->beConstructedThrough('create', [[
            'height' => 100,
        ]]);
        $this->shouldThrow(new \InvalidArgumentException("The parameters 'width' and 'height' are required for the resize operation."))
            ->duringInstantiation();
    }

    function it_can_not_be_constructed_without_height()
    {
        $this->beConstructedThrough('create', [[
            'width' => 100,
        ]]);
        $this->shouldThrow(new \InvalidArgumentException("The parameters 'width' and 'height' are required for the resize operation."))
            ->duringInstantiation();
    }

    function it_can_not_be_constructed_with_non_integer_values()
    {
        $this->beConstructedThrough('create', [[
            'width' => 'foo',
            'height' => 100,
        ]]);
        $this->shouldThrow(new \InvalidArgumentException("Parameter 'width' must be an integer."))
            ->duringInstantiation();

        $this->beConstructedThrough('create', [[
            'width' => 200,
            'height' => null,
        ]]);
        $this->shouldThrow(new \InvalidArgumentException("Parameter 'height' must be an integer."))
            ->duringInstantiation();

    }

    function it_can_not_be_constructed_with_unknown_parameter()
    {
        $this->beConstructedThrough('create', [[
            'width' => 200,
            'height' => 100,
            'foo' => 'bar',
        ]]);
        $this->shouldThrow(new \InvalidArgumentException('The property "foo" was not expected.'))
            ->duringInstantiation();
    }
}
