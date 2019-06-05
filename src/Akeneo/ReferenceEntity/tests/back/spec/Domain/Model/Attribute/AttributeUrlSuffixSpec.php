<?php
declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Domain\Model\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeUrlSuffix;
use PhpSpec\ObjectBehavior;


/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeUrlSuffixSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromString', ['/500x500']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeUrlSuffix::class);
    }

    function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn('/500x500');
    }

    public function it_throws_if_it_is_created_with_an_empty_value()
    {
        $this->beConstructedThrough('fromString', ['']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
