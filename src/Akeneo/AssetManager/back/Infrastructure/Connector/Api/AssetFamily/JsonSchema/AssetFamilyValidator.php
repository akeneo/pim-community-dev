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
                /** /!\ /!\ /!\ /!\
                 * Crappy fix to remove the possibility of updating the image of the asset family on the API side.
                 * @todo : To remove if the functional decide to not have an image on the asset family
                 * @todo : Check the PR https://github.com/akeneo/pim-enterprise-dev/pull/6651 for real fix
                 */
//                'image' => [
//                    'type' => ['string', 'null']
//                ],
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
                                            'type' => ['string', 'array'],
                                        ],
                                        'channel' => [
                                            'type' => 'string',
                                        ],
                                        'locale' => [
                                            'type' => 'string',
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
                                            'type' => 'string',
                                        ],
                                        'locale' => [
                                            'type' => 'string',
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
                '_links' => [
                    'type' => 'object'
                ]
            ],
            'required' => ['code'],
            'additionalProperties' => false,
        ];
    }
}
