<?php

namespace spec\Pim\Bundle\CatalogBundle\Elasticsearch;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SearchQueryBuilderSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Elasticsearch\SearchQueryBuilder');
    }

    function it_generates_an_empty_query()
    {
        $this->getQuery()->shouldBeAnEmptyQuery();
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

    function it_adds_one_should_clause()
    {
        $this->addShould([
            'term' => ['family' => 'camcorders'],
        ]);
        $this->getQuery()->shouldReturn(
            [
                '_source' => ['identifier'],
                'query'   => [
                    'bool' => [
                        'should' => [
                            ['term' => ['family' => 'camcorders']],
                        ],
                        'minimum_should_match' => 1,
                    ],
                ],
            ]
        );
    }

    function it_adds_multiple_should_clauses()
    {
        $this->addShould([
            'term' => ['family' => 'camcorders'],
        ]);
        $this->addShould([
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
                        'should' => [
                            ['term' => ['family' => 'camcorders']],
                            [
                                'query_string' => [
                                    'default_field' => 'values.description-pim_catalog_text.ecommerce.en_US',
                                    'query'         => '*Best camcorder in town*',
                                ],

                            ],
                        ],
                        'minimum_should_match' => 1,
                    ],
                ],
            ]
        );
    }

    function it_adds_filter__must_not_and_shouldclauses()
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

        $this->addShould([
            'term' => [
                'categories' => [1, 2]
            ]
        ]);

        $this->addShould([
            'bool' => [
                'must_not' => [
                    'exists' => [
                        'field' => 'categories'
                    ]
                ]
            ]
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
                        'should' => [
                            [
                                'term' => [
                                    'categories' => [1, 2]
                                ]
                            ],
                            [
                                'bool' => [
                                    'must_not' => [
                                        'exists' => [
                                            'field' => 'categories'
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'minimum_should_match' => 1,
                    ],
                ],
            ]
        );
    }

    public function getMatchers()
    {
        return [
            'beAnEmptyQuery' => function ($subject) {
                return
                    is_array($subject) &&
                    isset($subject['_source']) &&
                    ['identifier'] === $subject['_source'] &&
                    isset($subject['query']) &&
                    $subject['query'] instanceof \stdClass
                ;
            }
        ];
    }
}
