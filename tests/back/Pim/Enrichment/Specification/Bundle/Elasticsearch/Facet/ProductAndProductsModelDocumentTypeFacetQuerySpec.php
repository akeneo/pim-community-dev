<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Facet;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Facet\ProductAndProductsModelDocumentTypeFacetQuery;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use PhpSpec\ObjectBehavior;

class ProductAndProductsModelDocumentTypeFacetQuerySpec extends ObjectBehavior
{
    function it_is_instantiable()
    {
        $this->beAnInstanceOf(ProductAndProductsModelDocumentTypeFacetQuery::class);
    }

    public function it_adds_the_facet_to_the_search_query(SearchQueryBuilder $searchQueryBuilder)
    {
        $searchQueryBuilder->addTermsAggregation('document_type_facet', 'document_type')->shouldBeCalledOnce();

        $this->addTo($searchQueryBuilder);
    }
}
