<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Connector\Api\Asset\JsonSchema;

use JsonSchema\Validator;

class SearchFiltersValidator
{
    public function validate(array $searchFilters): array
    {
        $validator = new Validator();
        $searchFiltersObject = Validator::arrayToObjectRecursive($searchFilters);

        $validator->validate($searchFiltersObject, $this->getJsonSchema());

        return $validator->getErrors();
    }

    private function getJsonSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'complete' => [
                    'type' => 'object',
                    'required' => ['operator', 'value', 'channel', 'locales'],
                    'properties' => [
                        'operator' => [
                            'type' => 'string',
                            'enum' => ['='],
                        ],
                        'value' => [
                            'type' => 'boolean',
                        ],
                        'channel' => [
                            'type' => 'string',
                        ],
                        'locales' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'string',
                            ],
                            'minItems' => 1
                        ],
                    ]
                ],
                'updated' => [
                    'type' => 'array',
                    'minItems' => 1,
                    'items' => [
                        'oneOf' => [
                            [
                                'type' => 'object',
                                'required' => ['operator', 'value'],
                                'properties' => [
                                    'operator' => [
                                        'type' => 'string',
                                        'enum' => ['>', '<']
                                    ],
                                    'value' => [
                                        'type' => 'string',
                                        'format' => 'date-time'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'object',
                                'required' => ['operator', 'value'],
                                'properties' => [
                                    'operator' => [
                                        'type' => 'string',
                                        'enum' => ['BETWEEN', 'NOT BETWEEN']
                                    ],
                                    'value' => [
                                        'type' => 'array',
                                        "minItems" => 2,
                                        "maxItems" => 2,
                                        'items' => [
                                            ['type' => 'string', 'format' => 'date-time'],
                                            ['type' => 'string', 'format' => 'date-time'],
                                        ],
                                    ]
                                ]
                            ],
                            [
                                'type' => 'object',
                                'required' => ['operator', 'value'],
                                'properties' => [
                                    'operator' => [
                                        'type' => 'string',
                                        'enum' => ['SINCE LAST N DAYS']
                                    ],
                                    'value' => [
                                        'type' => 'integer',
                                    ]
                                ]
                            ],
                        ]
                    ],
                ]
            ],
            'additionalProperties' => false
        ];
    }
}
