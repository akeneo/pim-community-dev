<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Facet;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ElasticsearchResult;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Facet\Facet;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Facet\ProductAndProductsModelDocumentTypeFacetFactory;
use PhpSpec\ObjectBehavior;

class ProductAndProductsModelDocumentTypeFacetFactorySpec extends ObjectBehavior
{
    function it_is_instantiable()
    {
        $this->beAnInstanceOf(ProductAndProductsModelDocumentTypeFacetFactory::class);
    }

    function it_builds_a_document_type_facet_from_a_query_result()
    {
        $result = new ElasticsearchResult(['aggregations' => [
            'document_type_facet' => [
                'buckets' => [
                    ['key' => 'key1', 'doc_count' => 5],
                    ['key' => 'key2', 'doc_count' => 3],
                ],
            ],
        ]]);

        $facet = $this->build($result);
        $facet->shouldBeAnInstanceOf(Facet::class);
        $facet->getCountForKey('key1')->shouldBe(5);
        $facet->getCountForKey('key2')->shouldBe(3);
    }

    function it_returns_null_when_the_document_type_aggregation_is_not_present_in_the_query_result()
    {
        $result = new ElasticsearchResult(['aggregations' => [
            'other_aggregation' => [
                'buckets' => [
                    ['key' => 'key1', 'doc_count' => 5],
                    ['key' => 'key2', 'doc_count' => 3],
                ],
            ],
        ]]);

        $this->build($result)->shouldBeNull();
    }

    function it_returns_null_when_the_no_aggregation_is_present_in_the_query_result()
    {
        $result = new ElasticsearchResult([]);

        $this->build($result)->shouldBeNull();
    }
}
