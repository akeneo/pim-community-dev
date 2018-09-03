<?php
declare(strict_types=1);

namespace spec\Akeneo\EnrichedEntity\Domain\Model;

use Akeneo\EnrichedEntity\Domain\Model\ChannelIdentifier;
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

    public function it_can_normalize_itself()
    {
        $this->normalize()->shouldReturn('print');
    }
}
