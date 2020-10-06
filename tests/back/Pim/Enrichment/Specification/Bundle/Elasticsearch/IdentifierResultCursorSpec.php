<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ElasticsearchResult;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Component\Product\Query\ResultAwareInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ResultInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use PhpSpec\ObjectBehavior;

class IdentifierResultCursorSpec extends ObjectBehavior
{
    function let(IdentifierResult $identifierResult1, IdentifierResult $identifierResult2)
    {
        $this->beConstructedWith([$identifierResult1, $identifierResult2], 42, new ElasticsearchResult([]));
    }

    function it_is_a_cursor()
    {
        $this->shouldImplement(CursorInterface::class);
    }

    function it_is_aware_of_the_result()
    {
        $this->shouldImplement(ResultAwareInterface::class);
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

    function it_returns_the_result()
    {
        $this->getResult()->shouldBeAnInstanceOf(ResultInterface::class);
    }
}
