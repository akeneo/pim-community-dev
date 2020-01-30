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

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ResolutionOperation;
use PhpSpec\ObjectBehavior;

class ResolutionOperationSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('create', [[
            'resolution-x' => 200,
            'resolution-y' => 100,
            'resolution-unit' => 'ppc',
        ]]);
    }

    function it_is_an_operation()
    {
        $this->beAnInstanceOf(Operation::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ResolutionOperation::class);
    }

    function it_returns_its_type()
    {
        $this::getType()->shouldBe('resolution');
    }

    function it_can_be_constructed_with_ppi_resolution_unit()
    {
        $this->beConstructedThrough('create', [[
            'resolution-x' => 200,
            'resolution-y' => 100,
            'resolution-unit' => 'ppi',
        ]]);
        $this->getResolutionX()->shouldBe(200);
        $this->getResolutionY()->shouldBe(100);
        $this->getResolutionUnit()->shouldBe('ppi');
    }

    function it_cannot_be_constructed_without_resolution_x()
    {
        $this->beConstructedThrough('create', [[
            'resolution-y' => 100,
            'resolution-unit' => 'ppi',
        ]]);
        $this->shouldThrow(new \LogicException("The parameters 'resolution-x', 'resolution-y' and 'resolution-unit' are required for the resolution operation."))
            ->duringInstantiation();
    }

    function it_cannot_be_constructed_without_resolution_y()
    {
        $this->beConstructedThrough('create', [[
            'resolution-x' => 200,
            'resolution-unit' => 'ppc',
        ]]);
        $this->shouldThrow(new \LogicException("The parameters 'resolution-x', 'resolution-y' and 'resolution-unit' are required for the resolution operation."))
            ->duringInstantiation();
    }

    function it_cannot_be_constructed_with_non_ppc_or_ppi_resolution_unit()
    {
        $this->beConstructedThrough('create', [[
            'resolution-x' => 200,
            'resolution-y' => 100,
            'resolution-unit' => 'other',
        ]]);
        $this->shouldThrow(new \LogicException("Parameter 'resolution-unit' must be one of this values: 'ppc, ppi'. 'other' given."))
            ->duringInstantiation();
    }

    function it_cannot_be_constructed_with_resolution_x_equal_to_zero()
    {
        $this->beConstructedThrough('create', [[
            'resolution-x' => 0,
            'resolution-y' => 100,
            'resolution-unit' => 'ppi',
        ]]);
        $this->shouldThrow(new \LogicException("Parameter 'resolution-x' must be an integer greater than 0. '0' given."))
            ->duringInstantiation();
    }

    function it_can_not_be_constructed_with_resolution_y_equal_to_zero()
    {
        $this->beConstructedThrough('create', [[
            'resolution-x' => 200,
            'resolution-y' => 0,
            'resolution-unit' => 'ppi',
        ]]);
        $this->shouldThrow(new \LogicException("Parameter 'resolution-y' must be an integer greater than 0. '0' given."))
            ->duringInstantiation();
    }

    function it_cannot_be_constructed_with_null_resolution_x_and_null_resolution_y()
    {
        $this->beConstructedThrough('create', [[
            'resolution-x' => null,
            'resolution-y' => 10,
            'resolution-unit' => 'ppi',
        ]]);
        $this->shouldThrow(new \LogicException("Parameter 'resolution-x' must be an integer."))
            ->duringInstantiation();

        $this->beConstructedThrough('create', [[
            'resolution-x' => 10,
            'resolution-y' => null,
            'resolution-unit' => 'ppi',
        ]]);
        $this->shouldThrow(new \LogicException("Parameter 'resolution-y' must be an integer."))
            ->duringInstantiation();
    }

    function it_cannot_be_constructed_without_resolution_unit()
    {
        $this->beConstructedThrough('create', [[
            'resolution-x' => 10,
            'resolution-y' => 20,
        ]]);
        $this->shouldThrow(new \LogicException("The parameters 'resolution-x', 'resolution-y' and 'resolution-unit' are required for the resolution operation."))
            ->duringInstantiation();
    }

    function it_cannot_be_constructed_without_valid_parameter_types()
    {
        $this->beConstructedThrough('create', [[
            'resolution-x' => 'test',
            'resolution-y' => 100,
            'resolution-unit' => 'ppi',
        ]]);
        $this->shouldThrow(new \LogicException("Parameter 'resolution-x' must be an integer."))->duringInstantiation();

        $this->beConstructedThrough('create', [[
            'resolution-x' => 200,
            'resolution-y' => 'test',
            'resolution-unit' => 'ppi',
        ]]);
        $this->shouldThrow(new \LogicException("Parameter 'resolution-y' must be an integer."))->duringInstantiation();

        $this->beConstructedThrough('create', [[
            'resolution-x' => 200,
            'resolution-y' => 100,
            'resolution-unit' => 12,
        ]]);
        $this->shouldThrow(new \LogicException("Parameter 'resolution-unit' must be a string."))->duringInstantiation();
    }

    function it_cannot_be_constructed_with_unknown_parameter()
    {
        $this->beConstructedThrough('create', [[
            'resolution-x' => 100,
            'resolution-y' => 100,
            'resolution-unit' => 'ppi',
            'foo' => 'bar',
        ]]);
        $this->shouldThrow(new \InvalidArgumentException('The property "foo" was not expected.'))
            ->duringInstantiation();
    }
}
