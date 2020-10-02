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
        $this->beConstructedThrough('createEmptyWithName', ['the name', []]);
    }

    function it_is_a_facet()
    {
        $this->beAnInstanceOf(Facet::class);
    }

    function it_stores_facet_items_and_returns_count_for_a_given_key()
    {
        $item1 = FacetItem::fromArray(['key' => 'key1', 'doc_count' => 12]);
        $item2 = FacetItem::fromArray(['key' => 'key2', 'doc_count' => 44]);

        $this->addFacetItem($item1);
        $this->addFacetItem($item2);

        $this->getCountForKey('key1')->shouldReturn(12);
        $this->getCountForKey('key2')->shouldReturn(44);
        $this->getCountForKey('unknown')->shouldReturn(0);
    }
}
