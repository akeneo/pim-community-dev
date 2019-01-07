<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\OptionMapping;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class OptionMappingSpec extends ObjectBehavior
{
    public function let(): void
    {
        $optionData = [
            'status' => 'pending',
            'from' => ['id' => 'foo'],
            'to' => null,
        ];
        $this->beConstructedWith($optionData);
    }

    public function it_is_an_attribte_option_mapping(): void
    {
        $this->shouldHaveType(OptionMapping::class);
    }

    public function it_raises_an_exception_if_the_status_is_incorrect(): void
    {
        $this->beConstructedWith(['from' => ['id' => 'foo'], 'to' => null]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();

        $this->beConstructedWith(['from' => ['id' => 'foo'], 'to' => null, 'status' => 'foo']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_raises_an_exception_if_the_api_from_information_is_incorrect(): void
    {
        $optionData = [
            'status' => 'pending',
            'to' => null,
        ];
        $this->beConstructedWith($optionData);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();

        $optionData = [
            'status' => 'pending',
            'from' => [],
            'to' => null,
        ];
        $this->beConstructedWith($optionData);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();

        $optionData = [
            'status' => 'pending',
            'from' => ['id' => []],
            'to' => null,
        ];
        $this->beConstructedWith($optionData);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_raises_an_exception_if_the_api_to_information_is_incorrect(): void
    {
        $optionData = [
            'status' => 'active',
            'from' => ['id' => 'foo'],
        ];
        $this->beConstructedWith($optionData);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();

        $optionData = [
            'status' => 'active',
            'from' => ['id' => 'foo'],
            'to' => [],
        ];
        $this->beConstructedWith($optionData);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_returns_the_status(): void
    {
        $optionData = [
            'status' => 'active',
            'from' => ['id' => 'foo'],
            'to' => ['id' => 'bar'],
        ];
        $this->beConstructedWith($optionData);
        $this->getStatus()->shouldReturn('active');
    }

    public function it_returns_the_franklin_option_id(): void
    {
        $optionData = [
            'status' => 'active',
            'from' => ['id' => 'foo'],
            'to' => ['id' => 'bar'],
        ];
        $this->beConstructedWith($optionData);
        $this->getFranklinOptionId()->shouldReturn('foo');
    }

    public function it_returns_the_franklin_option_label(): void
    {
        $optionData = [
            'status' => 'active',
            'from' => ['id' => 'foo', 'label' => ['en_US' => 'My Foo']],
            'to' => ['id' => 'bar'],
        ];
        $this->beConstructedWith($optionData);
        $this->getFranklinOptionLabel()->shouldReturn('My Foo');
    }

    public function it_returns_the_pim_option(): void
    {
        $optionData = [
            'status' => 'active',
            'from' => ['id' => 'foo'],
            'to' => ['id' => 'bar'],
        ];
        $this->beConstructedWith($optionData);
        $this->getPimOption()->shouldReturn('bar');
    }
}
