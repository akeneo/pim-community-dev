<?php

namespace spec\Pim\Bundle\CatalogBundle\Elasticsearch;

use PhpSpec\ObjectBehavior;

class SearchQueryBuilderSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Elasticsearch\SearchQueryBuilder');
    }

    function it_generates_an_empty_query()
    {
        $this->getQuery()->shouldReturn(
            [
                '_source' => ['identifier'],
                'query'   => [],
            ]
        );
    }

    function it_adds_one_filter_clause()
    {
        $this->addFilter([
            'term' => ['family' => 'camcorders'],
        ]);

        $this->getQuery()->shouldReturn(
            [
                '_source' => ['identifier'],
                'query'   => [
                    'bool' => [
                        'filter' => [
                            ['term' => ['family' => 'camcorders']],
                        ],
                    ],
                ],
            ]
        );
    }

    function it_adds_multiple_filter_clauses()
    {
        $this->addFilter([
            'term' => ['family' => 'camcorders'],
        ]);
        $this->addFilter([
            'query_string' => [
                'default_field' => 'values.description-pim_catalog_text.ecommerce.en_US',
                'query'         => '*Best camcorder in town*',
            ],
        ]);

        $this->getQuery()->shouldReturn(
            [
                '_source' => ['identifier'],
                'query'   => [
                    'bool' => [
                        'filter' => [
                            ['term' => ['family' => 'camcorders']],
                            [
                                'query_string' => [
                                    'default_field' => 'values.description-pim_catalog_text.ecommerce.en_US',
                                    'query'         => '*Best camcorder in town*',
                                ],

                            ],
                        ],
                    ],
                ],
            ]
        );
    }

    function it_adds_one_must_not_clause()
    {
        $this->addMustNot([
            'term' => ['family' => 'camcorders'],
        ]);
        $this->getQuery()->shouldReturn(
            [
                '_source' => ['identifier'],
                'query'   => [
                    'bool' => [
                        'must_not' => [
                            ['term' => ['family' => 'camcorders']],
                        ],
                    ],
                ],
            ]
        );
    }

    function it_adds_multiple_must_not_clauses()
    {
        $this->addMustNot([
            'term' => ['family' => 'camcorders'],
        ]);
        $this->addMustNot([
            'query_string' => [
                'default_field' => 'values.description-pim_catalog_text.ecommerce.en_US',
                'query'         => '*Best camcorder in town*',
            ],
        ]);

        $this->getQuery()->shouldReturn(
            [
                '_source' => ['identifier'],
                'query'   => [
                    'bool' => [
                        'must_not' => [
                            ['term' => ['family' => 'camcorders']],
                            [
                                'query_string' => [
                                    'default_field' => 'values.description-pim_catalog_text.ecommerce.en_US',
                                    'query'         => '*Best camcorder in town*',
                                ],

                            ],
                        ],
                    ],
                ],
            ]
        );
    }

    function it_adds_filter_and_must_not_clauses()
    {
        $this->addFilter([
            'term' => ['family' => 'camcorders'],
        ]);
        $this->addFilter([
            'query_string' => [
                'default_field' => 'values.description-pim_catalog_text.ecommerce.en_US',
                'query'         => '*Best camcorder in town*',
            ],
        ]);

        $this->addMustNot([
            'range' => [
                'values.price-pim_catalog_price.<all_channels>.<all_locales>' => ['lte' => 500],
            ],
        ]);
        $this->addMustNot([
            'term' => [
                'name' => 'cheap',
            ],
        ]);

        $this->getQuery()->shouldReturn(
            [
                '_source' => ['identifier'],
                'query'   => [
                    'bool' => [
                        'filter'   => [
                            [
                                'term' => ['family' => 'camcorders'],
                            ],
                            [
                                'query_string' => [
                                    'default_field' => 'values.description-pim_catalog_text.ecommerce.en_US',
                                    'query'         => '*Best camcorder in town*',
                                ],
                            ],
                        ],
                        'must_not' => [
                            [
                                'range' => [
                                    'values.price-pim_catalog_price.<all_channels>.<all_locales>' => ['lte' => 500],
                                ],
                            ],
                            [
                                'term' => [
                                    'name' => 'cheap',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
    }
}
