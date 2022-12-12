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

/**
 * Validate the first level properties of a record using JSON Schema.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordPropertiesValidator
{
    public function validate(array $normalizedRecord): array
    {
        if (isset($normalizedRecord['values']) && [] === $normalizedRecord['values']) {
            $normalizedRecord['values'] = (object)[];
        }
        $validator = new Validator();
        $validator->setMaxErrors(50);

        $result = $validator->validate(
            Helper::toJSON($normalizedRecord),
            Helper::toJSON($this->getJsonSchema()),
        );

        if (!$result->hasError()) {
            return [];
        }

        $errorFormatter = new ErrorFormatter();

        $customFormatter = fn (ValidationError $error) => [
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
                '_links' => [
                    'type' => 'object'
                ],
                'code' => [
                    'type' => ['string'],
                ],
                'values' => [
                    'type' => 'object',
                    'patternProperties' => [
                        '.+' => ['type' => 'array'],
                    ],
                ],
                'created' => [
                    'type' => ['string'],
                ],
                'updated' => [
                    'type' => ['string'],
                ],
            ],
            'required' => ['code'],
            'additionalProperties' => false,
        ];
    }
}
