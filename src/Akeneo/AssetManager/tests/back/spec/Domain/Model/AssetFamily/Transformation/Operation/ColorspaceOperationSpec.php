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

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ColorspaceOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation;
use PhpSpec\ObjectBehavior;

class ColorspaceOperationSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('create', [[
            'colorspace' => 'grey',
        ]]);
    }

    function it_is_an_operation()
    {
        $this->beAnInstanceOf(Operation::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ColorspaceOperation::class);
    }

    function it_returns_its_type()
    {
        $this::getType()->shouldBe('colorspace');
    }

    function it_can_be_constructed_with_grey_colorspace()
    {
        $this->getColorspace()->shouldBe('grey');
    }

    function it_can_be_constructed_with_cmyk_colorspace()
    {
        $this->beConstructedThrough('create', [[
            'colorspace' => 'cmyk',
        ]]);
        $this->getColorspace()->shouldBe('cmyk');
    }

    function it_can_be_constructed_with_rgb_colorspace()
    {
        $this->beConstructedThrough('create', [[
            'colorspace' => 'rgb',
        ]]);
        $this->getColorspace()->shouldBe('rgb');
    }

    function it_can_not_be_constructed_with_unknown_colorspace()
    {
        $this->beConstructedThrough('create', [[
            'colorspace' => 'unknown',
        ]]);
        $this->shouldThrow(new \InvalidArgumentException("Parameter 'colorspace' must be one of this values: 'grey, cmyk, rgb'. 'unknown' given."))
            ->duringInstantiation();
    }

    function it_can_not_be_constructed_with_non_string_colorspace()
    {
        $this->beConstructedThrough('create', [[
            'colorspace' => 12,
        ]]);
        $this->shouldThrow(new \InvalidArgumentException("Parameter 'colorspace' must be a string."))
            ->duringInstantiation();
    }

    function it_can_not_be_constructed_without_colorspace_parameter()
    {
        $this->beConstructedThrough('create', [[
            'whatever' => 'rgb',
        ]]);
        $this->shouldThrow(new \InvalidArgumentException("The parameter 'colorspace' is required for the colorspace operation."))
            ->duringInstantiation();
    }

    function it_can_not_be_constructed_with_unknown_parameter()
    {
        $this->beConstructedThrough('create', [[
            'colorspace' => 'rgb',
            'foo' => 'bar',
        ]]);
        $this->shouldThrow(new \InvalidArgumentException('The property "foo" was not expected.'))
            ->duringInstantiation();
    }
}
