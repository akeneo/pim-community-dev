<?php
declare(strict_types=1);

namespace spec\Akeneo\EnrichedEntity\Domain\Model;

use Akeneo\EnrichedEntity\Domain\Model\LocaleIdentifier;
use PhpSpec\ObjectBehavior;

class LocaleIdentifierSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('fromCode', ['en_US']);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(LocaleIdentifier::class);
    }

    public function it_can_normalize_itself()
    {
        $this->normalize()->shouldReturn('en_US');
    }
}
