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

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ThumbnailOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation;
use PhpSpec\ObjectBehavior;

class ThumbnailOperationSpec extends ObjectBehavior
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
        $this->shouldHaveType(ThumbnailOperation::class);
        $this->getWidth()->shouldBe(200);
        $this->getHeight()->shouldBe(100);
    }

    function it_returns_its_type()
    {
        $this::getType()->shouldBe('thumbnail');
    }

    function it_can_be_constructed_with_width_or_height_parameter()
    {
        $object = $this::create([
            'width' => 200,
        ]);
        $object->beAnInstanceOf(ThumbnailOperation::class);
        $object->getWidth()->shouldBe(200);
        $object->getHeight()->shouldBeNull();

        $object = $this::create([
            'height' => 100,
        ]);
        $object->beAnInstanceOf(ThumbnailOperation::class);
        $object->getWidth()->shouldBeNull();
        $object->getHeight()->shouldBe(100);
    }

    function it_can_not_be_constructed_without_parameter()
    {
        $this->beConstructedThrough('create', [[]]);
        $this->shouldThrow(new \InvalidArgumentException("No parameter is provided for 'thumbnail' operation. At least one of parameter 'width' and 'height' must be defined."))
            ->duringInstantiation();
    }

    function it_can_not_be_constructed_without_valid_parameter_types()
    {
        $this->beConstructedThrough('create', [[
            'width' => '200px',
            'height' => 100,
        ]]);
        $this->shouldThrow(new \InvalidArgumentException("Parameter \"width\" must be an integer."))->duringInstantiation();

        $this->beConstructedThrough('create', [[
            'width' => 200,
            'height' => '100cm',
        ]]);
        $this->shouldThrow(new \InvalidArgumentException("Parameter \"height\" must be an integer."))->duringInstantiation();
    }

    function it_can_not_be_constructed_with_zero_values()
    {
        $this->beConstructedThrough('create', [[
            'width' => 0,
            'height' => 100,
        ]]);
        $this->shouldThrow(new \InvalidArgumentException("Parameter \"width\" must be greater than 0, \"0\" given."))->duringInstantiation();

        $this->beConstructedThrough('create', [[
            'width' => 200,
            'height' => -10,
        ]]);
        $this->shouldThrow(new \InvalidArgumentException("Parameter \"height\" must be greater than 0, \"-10\" given."))->duringInstantiation();
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
