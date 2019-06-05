<?php
declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Domain\Model\Attribute\Url;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\Url\Prefix;
use PhpSpec\ObjectBehavior;


/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class PrefixSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromString', ['http://www.binder.com']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Prefix::class);
    }

    function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn('http://www.binder.com');
    }

    public function it_throws_if_it_is_created_with_an_empty_value()
    {
        $this->beConstructedThrough('fromString', ['']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
