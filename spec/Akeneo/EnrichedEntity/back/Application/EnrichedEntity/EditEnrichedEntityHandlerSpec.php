<?php

namespace spec\Akeneo\EnrichedEntity\back\Application\EnrichedEntity;

use Akeneo\EnrichedEntity\back\Application\EnrichedEntity\EditEnrichedEntityHandler;
use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\back\Domain\Repository\EnrichedEntityRepository;
use PhpSpec\ObjectBehavior;

class EditEnrichedEntityHandlerSpec extends ObjectBehavior
{
    public function let(EnrichedEntityRepository $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(EditEnrichedEntityHandler::class);
    }

    function it_edits_an_enriched_entity() {
    }

    function it_throw_an_exception_if_the_identifier_is_malformed() {
    }

    function it_throw_an_exception_if_the_labels_are_malformed() {
    }
}
