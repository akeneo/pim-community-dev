<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Facet;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Facet\Facet;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Facet\FacetItem;
use PhpSpec\ObjectBehavior;

class FacetSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('create', ['the name', ['key1' => 12, 'key2' => 44]]);
    }

    function it_is_a_facet()
    {
        $this->beAnInstanceOf(Facet::class);
    }

    function it_returns_count_for_a_given_key()
    {
        $this->getCountForKey('key1')->shouldReturn(12);
        $this->getCountForKey('key2')->shouldReturn(44);
        $this->getCountForKey('unknown')->shouldReturn(0);
    }
}
