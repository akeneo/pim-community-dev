<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Domain\Model\Attribute\MediaLink;

use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Suffix;
use PhpSpec\ObjectBehavior;


/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class SuffixSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromString', ['/500x500']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Suffix::class);
    }

    function it_can_be_created_with_no_suffix()
    {
        $noSuffix = $this::createEmpty();
        $noSuffix->normalize()->shouldReturn(null);
    }

    function it_says_if_it_holds_no_suffix()
    {
        $this->isEmpty()->shouldReturn(false);
        $this::createEmpty()->isEmpty()->shouldReturn(true);
    }

    function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn('/500x500');
    }

    public function it_can_be_created_empty_from_string()
    {
        $this->beConstructedThrough('fromString', ['']);
        $this->normalize()->shouldReturn(null);
    }
}
