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

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Record\JsonSchema;

use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Helper;
use Opis\JsonSchema\Validator;

class SearchFiltersValidator
{
    public function validate(array $searchFilters): array
    {
        $validator = new Validator();
        $validator->setMaxErrors(50);

        $result = $validator->validate(
            Helper::toJSON($searchFilters),
            Helper::toJSON($this->getJsonSchema())
        );

        if (!$result->hasError()) {
            return [];
        }

        $errorFormatter = new ErrorFormatter();

        $customFormatter = static fn (ValidationError $error) => [
            'property' => $errorFormatter->formatErrorKey($error),
            'message' => $errorFormatter->formatErrorMessage($error),
        ];

        return $errorFormatter->formatFlat($result->error(), $customFormatter);
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
                            'contains' => [
                                'type' => 'string',
                            ],
                            'minItems' => 1,
                        ],
                    ],
                ],
                'updated' => [
                    'type' => 'array',
                    'minItems' => 1,
                    'oneOf' => [
                        [
                            'items' => [
                                'type' => 'object',
                                'required' => ['operator', 'value'],
                                'properties' => [
                                    'operator' => [
                                        'type' => 'string',
                                        'enum' => ['>', '<'],
                                    ],
                                    'value' => [
                                        'type' => 'string',
                                        'format' => 'date-time',
                                        '$filters' => [
                                            '$func' => 'min-date',
                                            '$vars' => [
                                                'value' => '1970-01-01',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'items' => [
                                'type' => 'object',
                                'required' => ['operator', 'value'],
                                'properties' => [
                                    'operator' => [
                                        'type' => 'string',
                                        'enum' => ['BETWEEN', 'NOT BETWEEN'],
                                    ],
                                    'value' => [
                                        'type' => 'array',
                                        'minItems' => 2,
                                        'maxItems' => 2,
                                        'contains' => [
                                            'type' => 'string',
                                            'format' => 'date-time',
                                        ],
                                        '$filters' => [
                                            '$func' => 'min-date',
                                            '$vars' => [
                                                'value' => '1970-01-01',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'items' => [
                                'type' => 'object',
                                'required' => ['operator', 'value'],
                                'properties' => [
                                    'operator' => [
                                        'type' => 'string',
                                        'enum' => ['SINCE LAST N DAYS'],
                                    ],
                                    'value' => [
                                        'type' => 'integer',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'code' => [
                    'type' => 'array',
                    'minItems' => 1,
                    'contains' => [
                        'type' => 'object',
                        'required' => ['operator', 'value'],
                        'properties' => [
                            'operator' => [
                                'type' => 'string',
                                'enum' => ['IN'],
                            ],
                            'value' => [
                                'type' => 'array',
                                'minItems' => 1,
                                'items' => [
                                    'type' => 'string',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'additionalProperties' => false,
        ];
    }
}
