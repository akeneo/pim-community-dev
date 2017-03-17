<?php

namespace spec\Akeneo\Bundle\ElasticsearchBundle\IndexConfiguration;

use PhpSpec\ObjectBehavior;

class LoaderSpec extends ObjectBehavior
{
    function it_loads_the_configuration_from_a_single_file()
    {
        $this->beConstructedWith([__DIR__ . '/conf1.yml']);

        $indexConfiguration = $this->load();
        $indexConfiguration->getSettings()->shouldReturn(
            [
                'analysis' => [
                    'char_filter' => [
                        'newline_pattern' => [
                            'type'        => 'pattern_replace',
                            'pattern'     => '\\n',
                            'replacement' => ''
                        ]
                    ]
                ]
            ]
        );
        $indexConfiguration->getMappings()->shouldReturn(
            [
                'an_index_type1' => [
                    'properties' => [
                        'name'    => [
                            'properties' => [
                                'last' => [
                                    'type' => 'text'
                                ]
                            ]
                        ],
                        'user_id' => [
                            'type'         => 'keyword',
                            'ignore_above' => 100,
                        ],
                    ]
                ]
            ]
        );
        $indexConfiguration->getAliases()->shouldReturn([]);
    }

    function it_loads_the_configuration_from_multiple_files()
    {
        $this->beConstructedWith(
            [
                __DIR__ . '/conf1.yml',
                __DIR__ . '/conf2.yml',
                __DIR__ . '/conf3.yml',
            ]
        );

        $indexConfiguration = $this->load();
        $indexConfiguration->getSettings()->shouldReturn(
            [
                'analysis' => [
                    'char_filter' => [
                        'newline_pattern' => [
                            'type'        => 'pattern_replace',
                            'pattern'     => '\\n',
                            'replacement' => ''
                        ]
                    ]
                ],
                'index'    => [
                    'number_of_shards'   => 3,
                    'number_of_replicas' => 2,
                ]
            ]
        );
        $indexConfiguration->getMappings()->shouldReturn(
            [
                'an_index_type1' => [
                    'properties'           => [
                        'name'                  => [
                            'properties' => [
                                'last' => [
                                    'type' => 'text'
                                ]
                            ]
                        ],
                        'user_id'               => [
                            'type'         => 'keyword',
                            'ignore_above' => 100,
                        ],
                        'just_another_property' => null
                    ],
                    'just_another_mapping' => null
                ],
                'an_index_type2' => [
                    'just_a_mapping' => null
                ],
                'an_index_type3' => [
                    'just_a_mapping' => null
                ],
            ]
        );
        $indexConfiguration->getAliases()->shouldReturn(
            [
                'alias_1' => [],
                'alias_2' => [
                    'filter'  => [
                        'term' => [
                            'user' => 'kimchy'
                        ]
                    ],
                    'routing' => 'kimchy'
                ],
            ]
        );
    }

    function it_loads_the_compiled_configuration_from_multiple_files()
    {
        $this->beConstructedWith(
            [
                __DIR__ . '/conf1.yml',
                __DIR__ . '/conf2.yml',
                __DIR__ . '/conf3.yml',
            ]
        );

        $indexConfiguration = $this->load();
        $indexConfiguration->buildAggregated()->shouldReturn([
            'settings' =>
                [
                    'analysis' => [
                        'char_filter' => [
                            'newline_pattern' => [
                                'type'        => 'pattern_replace',
                                'pattern'     => '\\n',
                                'replacement' => ''
                            ]
                        ]
                    ],
                    'index'    => [
                        'number_of_shards'   => 3,
                        'number_of_replicas' => 2,
                    ]
                ],
            'mappings' =>
                [
                    'an_index_type1' => [
                        'properties'           => [
                            'name'                  => [
                                'properties' => [
                                    'last' => [
                                        'type' => 'text'
                                    ]
                                ]
                            ],
                            'user_id'               => [
                                'type'         => 'keyword',
                                'ignore_above' => 100,
                            ],
                            'just_another_property' => null
                        ],
                        'just_another_mapping' => null
                    ],
                    'an_index_type2' => [
                        'just_a_mapping' => null
                    ],
                    'an_index_type3' => [
                        'just_a_mapping' => null
                    ],
                ],
            'aliases' =>
                [
                    'alias_1' => [],
                    'alias_2' => [
                        'filter'  => [
                            'term' => [
                                'user' => 'kimchy'
                            ]
                        ],
                        'routing' => 'kimchy'
                    ],
                ]
        ]);
    }

    function it_throws_an_exception_when_a_file_is_not_readable()
    {
        $this->beConstructedWith(
            [
                __DIR__ . '/conf1.yml',
                __DIR__ . '/do_not_exists.yml',
                __DIR__ . '/conf2.yml',
            ]
        );

        $this->shouldThrow('\Exception')->during('load');
    }
}
