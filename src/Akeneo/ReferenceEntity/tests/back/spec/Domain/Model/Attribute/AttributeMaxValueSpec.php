<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Domain\Model\Attribute;

use PhpSpec\ObjectBehavior;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeMaxValueSpec extends ObjectBehavior
{
    public function it_is_created_with_a_positive_max_value()
    {
        $this->beConstructedThrough('fromString', ['150']);
        $this->normalize()->shouldReturn('150');
    }

    public function it_is_created_with_a_negative_max_value()
    {
        $this->beConstructedThrough('fromString', ['-150']);
        $this->normalize()->shouldReturn('-150');
    }

    public function it_is_created_with_no_maximum()
    {
        $this->beConstructedThrough('noMaximum');
        $this->normalize()->shouldReturn(null);
    }

    public function it_throws_if_it_is_created_with_an_empty_value()
    {
        $this->beConstructedThrough('fromString', ['']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
