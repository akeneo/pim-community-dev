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
        $validator = new Validator();
        $normalizedAssetObject = Validator::arrayToObjectRecursive($normalizedAsset);
        $validator->validate($normalizedAssetObject, $this->getJsonSchema());

        return $validator->getErrors();
    }

    private function getJsonSchema(): array
    {
        return [
            'type' => 'object',
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
                'image' => [
                    'type' => ['string', 'null']
                ],
                'rule_templates' => [
                    'type'  => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'conditions' => [
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
                                            'type' => 'string',
                                        ],
                                    ],
                                    'required' => ['field', 'operator', 'value'],
                                    'additionalProperties' => false,
                                ],
                            ],
                            'actions' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'type' => [
                                            'type' => 'string',
                                        ],
                                        'field' => [
                                            'type' => 'string',
                                        ],
                                        'value' => [
                                            'type' => 'string',
                                        ],
                                    ],
                                    'required' => ['type', 'field', 'value'],
                                    'additionalProperties' => false,
                                ],
                            ]
                        ],
                        'required' => ['conditions', 'actions'],
                        'additionalProperties' => false,
                    ],
                ],
                '_links' => [
                    'type' => 'object'
                ]
            ],
            'required' => ['code'],
            'additionalProperties' => false,
        ];
    }
}
