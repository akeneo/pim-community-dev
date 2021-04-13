<?php

declare(strict_types=1);


namespace Akeneo\Pim\TailoredExport\Connector;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;

class DefaultValueProvider implements DefaultValuesProviderInterface
{
    private DefaultValuesProviderInterface $simpleProvider;
    private array $supportedJobNames;

    public function __construct(
        DefaultValuesProviderInterface $simpleProvider,
        array $supportedJobNames
    ) {
        $this->simpleProvider = $simpleProvider;
        $this->supportedJobNames = $supportedJobNames;
    }

    public function getDefaultValues()
    {
        $parameters = $this->simpleProvider->getDefaultValues();
        $parameters['filters'] = [
            'data' => [
                [
                    'field' => 'enabled',
                    'operator' => Operators::EQUALS,
                    'value' => true,
                ],
                //                [
                //                    'field' => 'completeness',
                //                    'operator' => Operators::GREATER_OR_EQUAL_THAN,
                //                    'value' => 100,
                //                ],
                [
                    'field' => 'categories',
                    'operator' => Operators::IN_CHILDREN_LIST,
                    'value' => ['master']
                ]
            ],
        ];

        /*
         *
         *
         *
         */
        $parameters['columns'] = [
            [
                'target' => 'name',
                'sources' => [
                    [
                        'uuid' => '0001',
                        'code' => 'name',
                        'locale' => null,
                        'channel' => null,
                        'operations' => [
                            [
                                'type' => 'default_value',
                                'value' => 'foo',
                            ],
                            [
                                'type' => 'replace',
                                'mapping' => [
                                    'Bag' => 'sac a pied',
                                ],
                            ],
                        ],
                    ],
                    [
                        'uuid' => '0002',
                        'code' => 'name',
                        'locale' => null,
                        'channel' => null,
                        'operations' => [
                            [
                                'type' => 'default_value',
                                'value' => 'toto',
                            ],
                        ],
                    ],
                ],
                'format' => [
                    'type' => 'concat',
                    'elements' => [
                        [
                            'type' => 'source',
                            'value' => '0001',
                        ],
                        [
                            'type' => 'source',
                            'value' => '0002',
                        ],
                        [
                            'type' => 'string',
                            'value' => 'cm',
                        ],
                    ],
                ],
            ],
            [
                'target' => 'weight',
                'sources' => [
                    [
                        'uuid' => '0001',
                        'code' => 'weight',
                        'locale' => null,
                        'channel' => null,
                        'operations' => [
                            [
                                'type' => 'default_value',
                                'value' => 'toto',
                            ],
                            [
                                 'type' => 'convert',
                                 'unit' => 'MILLIGRAM',
                            ],
                        ],
                        'selection' => [
                            'type' => 'amount', // unit_code, unit_label, amount
                        ],
                    ],
                    [
                        'uuid' => '0002',
                        'code' => 'weight',
                        'locale' => null,
                        'channel' => null,
                        'operations' => [
                            [
                                'type' => 'convert',
                                'unit' => 'MILLIGRAM',
                            ],
                        ],
                        'selection' => [
                            'type' => 'unit_label',
                            'locale' => 'fr_FR'
                        ],
                    ],
                ],
                'format' => [
                    'type' => 'concat',
                    'elements' => [
                        [
                            'type' => 'source',
                            'value' => '0001',
                        ],
                        [
                            'type' => 'string',
                            'value' => ' ',
                        ],
                        [
                            'type' => 'source',
                            'value' => '0002',
                        ],
                    ],
                ],
            ],
            [
                'target' => 'weight-customized',
                'sources' => [
                    [
                        'uuid' => '0001',
                        'code' => 'weight',
                        'locale' => null,
                        'channel' => null,
                        'operations' => [
                            [
                                'type' => 'convert',
                                'unit' => 'MILLIGRAM',
                            ],
                        ],
                        'selection' => [
                            'type' => 'amount',
                        ],
                    ],
                ],
                'format' => [
                    'type' => 'concat',
                    'elements' => [
                        [
                            'type' => 'source',
                            'value' => '0001',
                        ],
                    ],
                ],
            ],
            [
                'target' => 'weight-unit-customized',
                'sources' => [],
                'format' => [
                    'type' => 'concat',
                    'elements' => [
                        [
                            'type' => 'string',
                            'value' => 'MILLIGRAM',
                        ],
                    ],
                ],
            ],
        ];

        return $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job)
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
