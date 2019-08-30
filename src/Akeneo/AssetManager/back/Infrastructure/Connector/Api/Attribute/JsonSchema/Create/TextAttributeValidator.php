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

namespace Akeneo\AssetManager\Infrastructure\Connector\Api\Attribute\JsonSchema\Create;

use JsonSchema\Validator;

class TextAttributeValidator implements AttributeValidatorInterface
{
    private const API_TEXT_ATTRIBUTE_TYPE = 'text';

    public function validate(array $normalizedAttribute): array
    {
        $normalizedAttribute['labels'] =  empty($normalizedAttribute['labels']) ? (object) [] : $normalizedAttribute['labels'] ;
        $asset = Validator::arrayToObjectRecursive($normalizedAttribute);
        $validator = new Validator();
        $validator->validate($asset, $this->getJsonSchema());

        return $validator->getErrors();
    }

    public function forAttributeTypes(): array
    {
        return [self::API_TEXT_ATTRIBUTE_TYPE];
    }

    private function getJsonSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['code', 'type'],
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
                    'type' => [ 'boolean'],
                ],
                'value_per_channel' => [
                    'type' => [ 'boolean'],
                ],
                'is_required_for_completeness' => [
                    'type' => [ 'boolean'],
                ],
                'is_textarea' => [
                    'type' => [ 'boolean'],
                ],
                'is_rich_text_editor' => [
                    'type' => [ 'boolean'],
                ],
                'max_characters' => [
                    'type' => [ 'integer', 'null'],
                ],
                'validation_rule' => [
                    'type' => [ 'string'],
                ],
                'validation_regexp' => [
                    'type' => [ 'string', 'null'],
                ]
            ],
            'additionalProperties' => false,
        ];
    }
}
