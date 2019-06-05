<?php
declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Domain\Model\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeUrlType;
use PhpSpec\ObjectBehavior;


/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeUrlTypeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromString', ['image']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeUrlType::class);
    }

    function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn('image');
    }

    public function it_throws_if_it_is_created_with_an_empty_value()
    {
        $this->beConstructedThrough('fromString', ['']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_throws_if_it_is_created_with_a_non_existing_type()
    {
        $this->beConstructedThrough('fromString', ['test']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
