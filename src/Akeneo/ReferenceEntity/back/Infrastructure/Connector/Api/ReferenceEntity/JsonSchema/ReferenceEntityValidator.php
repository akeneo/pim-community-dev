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

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Api\ReferenceEntity\JsonSchema;

use JsonSchema\Validator;

class ReferenceEntityValidator
{
    public function validate(array $normalizedRecord): array
    {
        $validator = new Validator();
        $normalizedRecordObject = Validator::arrayToObjectRecursive($normalizedRecord);
        $validator->validate($normalizedRecordObject, $this->getJsonSchema());

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
            ],
            'required' => ['code'],
            'additionalProperties' => false,
        ];
    }
}
