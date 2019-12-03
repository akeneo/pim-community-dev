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

namespace Akeneo\AssetManager\Infrastructure\Connector\Api\AssetFamily\JsonSchema;

use JsonSchema\Validator;

class AssetFamilyValidator
{
    public function validate(array $normalizedAsset): array
    {
        $normalizedAsset['labels'] =  empty($normalizedAsset['labels']) ? (object) [] : $normalizedAsset['labels'] ;
        $validator = new Validator();
        $normalizedAssetObject = Validator::arrayToObjectRecursive($normalizedAsset);
        $validator->validate($normalizedAssetObject, $this->getJsonSchema());

        return $validator->getErrors();
    }

    private function getJsonSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['code'],
            'properties' => [
                'code' => [
                    'type' => ['string'],
                ],
                'labels' => [
                    'type' => 'object',
                    'patternProperties' => [
                        '.+' => ['type' => 'string'],
                    ],
                ],
                'attribute_as_main_media' => [
                    'type' => ['string'],
                ],
                'product_link_rules' => [
                    'type'  => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'product_selections' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'field' => [
                                            'type' => 'string',
                                        ],
                                        'operator' => [
                                            'type' => 'string',
                                        ],
                                        'value' => [
                                            'type' => ['string', 'array', 'boolean'],
                                        ],
                                        'channel' => [
                                            'type' => ['string', 'null'],
                                        ],
                                        'locale' => [
                                            'type' => ['string', 'null'],
                                        ],
                                    ],
                                    'required' => ['field', 'operator', 'value'],
                                    'additionalProperties' => false,
                                ],
                            ],
                            'assign_assets_to' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'attribute' => [
                                            'type' => 'string',
                                        ],
                                        'mode' => [
                                            'type' => 'string',
                                        ],
                                        'channel' => [
                                            'type' => ['string', 'null'],
                                        ],
                                        'locale' => [
                                            'type' => ['string', 'null'],
                                        ],
                                    ],
                                    'required' => ['attribute', 'mode'],
                                    'additionalProperties' => false,
                                ],
                            ]
                        ],
                        'required' => ['product_selections', 'assign_assets_to'],
                        'additionalProperties' => false,
                    ],
                ],
                'transformations' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'source' => [
                                'type' => 'object',
                                'properties' => [
                                    'attribute' => [
                                        'type' => 'string',
                                    ],
                                    'channel' => [
                                        'type' => ['string', 'null'],
                                    ],
                                    'locale' => [
                                        'type' => ['string', 'null'],
                                    ],
                                ],
                                'required' => ['attribute', 'channel', 'locale'],
                                'additionalProperties' => false,
                            ],
                            'target' => [
                                'type' => 'object',
                                'properties' => [
                                    'attribute' => [
                                        'type' => 'string',
                                    ],
                                    'channel' => [
                                        'type' => ['string', 'null'],
                                    ],
                                    'locale' => [
                                        'type' => ['string', 'null'],
                                    ],
                                ],
                                'required' => ['attribute', 'channel', 'locale'],
                                'additionalProperties' => false,
                            ],
                            'operations' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'type' => [
                                            'type' => 'string',
                                        ],
                                        'parameters' => [
                                            'type' => 'object',
                                        ],
                                        'required' => ['type'],
                                        'additionalProperties' => false,
                                    ],
                                ],
                            ],
                            'filename_prefix' => [
                                'type' => ['string', 'null'],
                            ],
                            'filename_suffix' => [
                                'type' => ['string', 'null'],
                            ],
                        ],
                        'required' => ['source', 'target', 'operations'],
                        'additionalProperties' => false,
                    ],
                ],
                '_links' => [
                    'type' => 'object'
                ]
            ],
            'additionalProperties' => false,
        ];
    }
}
