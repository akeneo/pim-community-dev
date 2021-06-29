<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class RankSpec extends ObjectBehavior
{
    public function it_can_be_constructed_from_a_rank_code()
    {
        $this->beConstructedThrough('fromString', ['rank_1']);

        $this->toInt()->shouldReturn(1);
    }

    public function it_throws_an_exception_if_the_code_is_invalid()
    {
        $this->beConstructedThrough('fromString', ['foo_1']);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_can_be_constructed_from_a_rank_value()
    {
        $this->beConstructedThrough('fromInt', [2]);

        $this->toInt()->shouldReturn(2);
    }

    public function it_throws_an_exception_if_the_value_is_invalid()
    {
        $this->beConstructedThrough('fromInt', [42]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_can_be_constructed_from_a_rank_letter()
    {
        $this->beConstructedThrough('fromLetter', ['C']);

        $this->toInt()->shouldReturn(3);
    }

    public function it_throws_an_exception_if_the_letter_is_invalid()
    {
        $this->beConstructedThrough('fromLetter', ['Z']);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_can_be_constructed_from_a_rate()
    {
        $this->beConstructedThrough('fromRate', [new Rate(61)]);

        $this->toInt()->shouldReturn(4);
    }
}
