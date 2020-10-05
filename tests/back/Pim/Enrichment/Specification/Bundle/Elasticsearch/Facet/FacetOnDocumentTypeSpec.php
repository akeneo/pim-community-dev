<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Facet;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Facet\FacetOnDocumentType;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Query\Facet\FacetOnDocumentTypeInterface;
use PhpSpec\ObjectBehavior;

class FacetOnDocumentTypeSpec extends ObjectBehavior
{
    function it_is_instantiable()
    {
        $this->beAnInstanceOf(FacetOnDocumentType::class);
    }

    function it_implements_facet_on_document_type_interface()
    {
        $this->beAnInstanceOf(FacetOnDocumentTypeInterface::class);
    }

    public function it_adds_the_facet_to_the_search_query(SearchQueryBuilder $searchQueryBuilder)
    {
        $searchQueryBuilder->addTermsAggregation('document_type_facet', 'document_type')->shouldBeCalledOnce();

        $this->add($searchQueryBuilder);
    }
}
