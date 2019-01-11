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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Exception\InvalidSuggestedValueException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SuggestedValue;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class SuggestedValueSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith('name', 'bar');
    }

    public function it_is_a_suggested_value(): void
    {
        $this->shouldHaveType(SuggestedValue::class);
    }

    public function it_has_an_attribute_code(): void
    {
        $this->pimAttributeCode()->shouldReturn('name');
    }

    public function it_has_a_value(): void
    {
        $this->value()->shouldReturn('bar');
    }

    public function it_throws_an_exception_if_name_is_empty(): void
    {
        $this->beConstructedWith('', 'bar');
        $this->shouldThrow(InvalidSuggestedValueException::class)->duringInstantiation();
    }

    public function it_throws_an_exception_if_value_is_an_empty_string(): void
    {
        $this->beConstructedWith('name', '');
        $this->shouldThrow(InvalidSuggestedValueException::class)->duringInstantiation();
    }

    public function it_throws_an_exception_if_value_is_an_empty_array(): void
    {
        $this->beConstructedWith('name', []);
        $this->shouldThrow(InvalidSuggestedValueException::class)->duringInstantiation();
    }

    public function it_throws_an_exception_if_value_is_not_a_string(): void
    {
        $this->beConstructedWith('name', new \stdClass());
        $this->shouldThrow(InvalidSuggestedValueException::class)->duringInstantiation();
    }

    public function it_throws_an_exception_if_value_is_not_an_array_of_strings(): void
    {
        $this->beConstructedWith('name', [new \stdClass()]);
        $this->shouldThrow(InvalidSuggestedValueException::class)->duringInstantiation();
    }
}
