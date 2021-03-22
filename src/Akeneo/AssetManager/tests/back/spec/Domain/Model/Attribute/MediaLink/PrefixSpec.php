<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Domain\Model\Attribute\MediaLink;

use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Prefix;
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

    function it_can_be_created_with_no_prefix()
    {
        $noPrefix = $this::createEmpty();
        $noPrefix->normalize()->shouldReturn(null);
    }

    function it_says_if_it_holds_no_prefix()
    {
        $this->isEmpty()->shouldReturn(false);
        $this::createEmpty()->isEmpty()->shouldReturn(true);
    }

    function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn('http://www.binder.com');
    }

    public function it_can_be_created_empty_from_string()
    {
        $this->beConstructedThrough('fromString', ['']);
        $this->normalize()->shouldReturn(null);
    }
}
