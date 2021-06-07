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

namespace spec\Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\OptimizeJpegOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation;
use PhpSpec\ObjectBehavior;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class OptimizeJpegOperationSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('create', [[
            'quality' => 50,
        ]]);
    }

    function it_is_an_operation()
    {
        $this->beAnInstanceOf(Operation::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(OptimizeJpegOperation::class);
    }

    function it_returns_its_type()
    {
        $this::getType()->shouldBe('optimize_jpeg');
    }

    function it_returns_quality()
    {
        $this->getQuality()->shouldBe(50);
    }

    function it_can_not_be_constructed_without_quality()
    {
        $this->beConstructedThrough('create', [[]]);
        $this->shouldThrow(new \InvalidArgumentException("The parameter 'quality' is required for the optimize jpeg operation."))
            ->duringInstantiation();
    }

    function it_can_not_be_constructed_with_a_quality_less_than_1()
    {
        $this->beConstructedThrough('create', [[
            'quality' => 0,
        ]]);
        $this->shouldThrow(new \InvalidArgumentException("Parameter 'quality' must be between 1 and 100."))
            ->duringInstantiation();
    }

    function it_can_not_be_constructed_with_a_quality_greater_than_100()
    {
        $this->beConstructedThrough('create', [[
            'quality' => 101,
        ]]);
        $this->shouldThrow(new \InvalidArgumentException("Parameter 'quality' must be between 1 and 100."))
            ->duringInstantiation();
    }

    function it_can_not_be_constructed_with_non_integer_quality()
    {
        $this->beConstructedThrough('create', [[
            'quality' => 'foo',
        ]]);
        $this->shouldThrow(new \InvalidArgumentException("Parameter 'quality' must be an integer."))
            ->duringInstantiation();
    }

    function it_can_not_be_constructed_with_unknown_parameter()
    {
        $this->beConstructedThrough('create', [[
            'quality' => 50,
            'foo' => 'bar',
        ]]);
        $this->shouldThrow(new \InvalidArgumentException('The property "foo" was not expected.'))
            ->duringInstantiation();
    }
}
