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
                '_links' => [
                    'type' => 'object'
                ]
            ],
            'required' => ['code'],
            'additionalProperties' => false,
        ];
    }
}
