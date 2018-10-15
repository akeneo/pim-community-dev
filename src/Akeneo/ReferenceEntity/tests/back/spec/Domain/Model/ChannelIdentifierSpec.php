<?php
declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Domain\Model;

use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use PhpSpec\ObjectBehavior;

class ChannelIdentifierSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('fromCode', ['print']);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ChannelIdentifier::class);
    }

    public function it_cannot_be_created_with_an_empty_string()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('fromCode', ['']);
    }

    public function it_can_normalize_itself()
    {
        $this->normalize()->shouldReturn('print');
    }

    public function it_tells_if_it_is_equals_to_another_channel_reference()
    {
        $this->equals(ChannelIdentifier::fromCode('print'))->shouldReturn(true);
        $this->equals(ChannelIdentifier::fromCode('mobile'))->shouldReturn(false);
    }
}
