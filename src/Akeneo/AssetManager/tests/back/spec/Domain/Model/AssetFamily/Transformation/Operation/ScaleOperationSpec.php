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

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ScaleOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation;
use PhpSpec\ObjectBehavior;

class ScaleOperationSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('create', [[
            'width' => 200,
            'height' => 100,
            'ratio' => 50,
        ]]);
    }

    function it_is_an_operation()
    {
        $this->beAnInstanceOf(Operation::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ScaleOperation::class);
        $this->getWidth()->shouldBe(200);
        $this->getHeight()->shouldBe(100);
        $this->getRatioPercent()->shouldBe(50);
    }

    function it_returns_its_type()
    {
        $this::getType()->shouldBe('scale');
    }

    function it_can_be_constructed_with_2_parameters()
    {
        $object = $this::create([
            'width' => 200,
            'height' => 100,
        ]);
        $object->beAnInstanceOf(ScaleOperation::class);
        $object->getWidth()->shouldBe(200);
        $object->getHeight()->shouldBe(100);
        $object->getRatioPercent()->shouldBeNull();

        $object = $this::create([
            'width' => 200,
            'ratio' => 50,
        ]);
        $object->beAnInstanceOf(ScaleOperation::class);
        $object->getWidth()->shouldBe(200);
        $object->getHeight()->shouldBeNull();
        $object->getRatioPercent()->shouldBe(50);

        $object = $this::create([
            'height' => 100,
            'ratio' => 50,
        ]);
        $object->beAnInstanceOf(ScaleOperation::class);
        $object->getWidth()->shouldBeNull();
        $object->getHeight()->shouldBe(100);
        $object->getRatioPercent()->shouldBe(50);
    }

    function it_can_be_constructed_with_1_parameter()
    {
        $object = $this::create([
            'width' => 200,
        ]);
        $object->beAnInstanceOf(ScaleOperation::class);
        $object->getWidth()->shouldBe(200);
        $object->getHeight()->shouldBeNull();
        $object->getRatioPercent()->shouldBeNull();

        $object = $this::create([
            'height' => 100,
        ]);
        $object->beAnInstanceOf(ScaleOperation::class);
        $object->getWidth()->shouldBeNull();
        $object->getHeight()->shouldBe(100);
        $object->getRatioPercent()->shouldBeNull();

        $object = $this::create([
            'ratio' => 50,
        ]);
        $object->beAnInstanceOf(ScaleOperation::class);
        $object->getWidth()->shouldBeNull();
        $object->getHeight()->shouldBeNull();
        $object->getRatioPercent()->shouldBe(50);
    }

    function it_can_not_be_constructed_without_parameter()
    {
        $this->beConstructedThrough('create', [[]]);
        $this->shouldThrow(new \InvalidArgumentException("No parameter is provided for 'scale' operation. At least one of parameter 'width', 'height' and 'ratio' must be defined."))
            ->duringInstantiation();
    }

    function it_can_not_be_constructed_without_valid_parameter_types()
    {
        $this->beConstructedThrough('create', [[
            'width' => '200px',
            'height' => 100,
            'ratio' => 50,
        ]]);
        $this->shouldThrow(new \InvalidArgumentException("Parameter \"width\" must be an integer."))->duringInstantiation();

        $this->beConstructedThrough('create', [[
            'width' => 200,
            'height' => '100cm',
            'ratio' => 50,
        ]]);
        $this->shouldThrow(new \InvalidArgumentException("Parameter \"height\" must be an integer."))->duringInstantiation();

        $this->beConstructedThrough('create', [[
            'width' => 200,
            'height' => 100,
            'ratio' => '50%',
        ]]);
        $this->shouldThrow(new \InvalidArgumentException("Parameter \"ratio\" must be an integer."))->duringInstantiation();
    }

    function it_can_not_be_constructed_with_zero_values()
    {
        $this->beConstructedThrough('create', [[
            'width' => 0,
            'height' => 100,
            'ratio' => 50,
        ]]);
        $this->shouldThrow(new \InvalidArgumentException("Parameter \"width\" must be greater than 0, \"0\" given."))->duringInstantiation();

        $this->beConstructedThrough('create', [[
            'width' => 200,
            'height' => 0,
            'ratio' => 50,
        ]]);
        $this->shouldThrow(new \InvalidArgumentException("Parameter \"height\" must be greater than 0, \"0\" given."))->duringInstantiation();

        $this->beConstructedThrough('create', [[
            'width' => 200,
            'height' => 100,
            'ratio' => -1,
        ]]);
        $this->shouldThrow(new \InvalidArgumentException("Parameter \"ratio\" must be greater than 0, \"-1\" given."))->duringInstantiation();
    }

    function it_can_not_be_constructed_with_unknown_parameter()
    {
        $this->beConstructedThrough('create', [[
            'ratio' => 90,
            'foo' => 'bar',
        ]]);
        $this->shouldThrow(new \InvalidArgumentException('The property "foo" was not expected.'))
            ->duringInstantiation();
    }
}
