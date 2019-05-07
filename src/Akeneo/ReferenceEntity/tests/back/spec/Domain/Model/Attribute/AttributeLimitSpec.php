<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Domain\Model\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeLimit;
use PhpSpec\ObjectBehavior;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeLimitSpec extends ObjectBehavior
{
    public function it_is_created_with_a_positive_value()
    {
        $this->beConstructedThrough('fromString', ['150']);
        $this->normalize()->shouldReturn('150');
    }

    public function it_is_created_with_a_negative_value()
    {
        $this->beConstructedThrough('fromString', ['-150']);
        $this->normalize()->shouldReturn('-150');
    }

    public function it_can_be_limit_less()
    {
        $this->beConstructedThrough('limitLess');
        $this->normalize()->shouldReturn(null);
    }

    public function it_throws_if_it_is_created_with_an_empty_value()
    {
        $this->beConstructedThrough('fromString', ['']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_tells_if_it_is_limitless()
    {
        $this->beConstructedThrough('limitLess');
        $this->isLimitLess()->shouldReturn(true);
    }

    public function it_tells_if_it_is_not_limitless()
    {
        $this->beConstructedThrough('fromString', ['-150']);
        $this->isLimitLess()->shouldReturn(false);
    }

    public function it_is_comparable()
    {
        $this->beConstructedThrough('fromString', ['0']);

        $this->isGreater(AttributeLimit::fromString('-1'))->shouldReturn(true);
        $this->isGreater(AttributeLimit::fromString('1'))->shouldReturn(false);
        $this->isLower(AttributeLimit::fromString('-1'))->shouldReturn(false);
        $this->isLower(AttributeLimit::fromString('1'))->shouldReturn(true);
    }

    public function it_cannot_tell_whether_a_limit_less_value_is_greater_than_another_one()
    {
        $this->beConstructedThrough('limitLess', []);
        $this->shouldThrow(\LogicException::class)
            ->during('isGreater', [AttributeLimit::fromString('-1')]);
    }

    public function it_cannot_tell_wether_a_limit_is_greater_than_a_limit_less_value()
    {
        $this->beConstructedThrough('fromString', ['1']);
        $this->shouldThrow(\LogicException::class)
            ->during('isGreater', [AttributeLimit::limitLess()]);
    }

    public function it_cannot_tell_whether_a_limit_less_value_is_lower_than_another_one()
    {
        $this->beConstructedThrough('limitLess', []);
        $this->shouldThrow(\LogicException::class)
            ->during('isLower', [AttributeLimit::fromString('-1')]);
    }

    public function it_cannot_tell_wether_a_limit_is_lower_than_a_limit_less_value()
    {
        $this->beConstructedThrough('fromString', ['1']);
        $this->shouldThrow(\LogicException::class)
            ->during('isLower', [AttributeLimit::limitLess()]);
    }
}
