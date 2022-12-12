<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Connector\Api\Attribute\JsonSchema\Create;

use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Helper;
use Opis\JsonSchema\Validator;

class MediaLinkAttributeValidator implements AttributeValidatorInterface
{
    private const API_URL_ATTRIBUTE_TYPE = 'media_link';

    public function validate(array $normalizedAttribute): array
    {
        $normalizedAttribute['labels'] =  empty($normalizedAttribute['labels']) ? (object) [] : $normalizedAttribute['labels'];
        $validator = new Validator();
        $validator->setMaxErrors(50);

        $result = $validator->validate(
            Helper::toJSON($normalizedAttribute),
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

    public function forAttributeTypes(): array
    {
        return [self::API_URL_ATTRIBUTE_TYPE];
    }

    private function getJsonSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['code', 'type', 'media_type'],
            'properties' => [
                'code' => [
                    'type' => ['string'],
                ],
                'type' => [
                    'type' => ['string'],
                ],
                'labels' => [
                    'type' => 'object',
                    'patternProperties' => [
                        '.+' => ['type' => 'string'],
                    ],
                ],
                'value_per_locale' => [
                    'type' => ['boolean'],
                ],
                'value_per_channel' => [
                    'type' => ['boolean'],
                ],
                'is_required_for_completeness' => [
                    'type' => ['boolean'],
                ],
                'is_read_only' => [
                    'type' => [ 'boolean'],
                ],
                'media_type' => [
                    'type' => ['string'],
                ],
                'prefix' => [
                    'type' => ['string', 'null'],
                ],
                'suffix' => [
                    'type' => ['string', 'null'],
                ],
            ],
            'additionalProperties' => false,
        ];
    }
}
