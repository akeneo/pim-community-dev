<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use PhpSpec\ObjectBehavior;

class IdentifierResultCursorSpec extends ObjectBehavior
{
    function let(IdentifierResult $identifierResult1, IdentifierResult $identifierResult2)
    {
        $this->beConstructedWith([$identifierResult1, $identifierResult2], 42);
    }

    function it_is_a_cursor()
    {
        $this->shouldImplement(CursorInterface::class);
    }

    function it_is_countable()
    {
        $this->count()->shouldBe(42);
    }

    function it_is_iterable($identifierResult1, $identifierResult2)
    {
        $this->rewind();
        $this->valid()->shouldReturn(true);
        $this->current()->shouldReturn($identifierResult1);

        $this->next();
        $this->valid()->shouldReturn(true);
        $this->current()->shouldReturn($identifierResult2);

        $this->next();
        $this->valid()->shouldReturn(false);
    }
}
