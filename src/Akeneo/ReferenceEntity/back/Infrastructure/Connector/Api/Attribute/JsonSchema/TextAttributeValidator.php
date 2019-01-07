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

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Attribute\JsonSchema;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use JsonSchema\Validator;

class TextAttributeValidator implements AttributeValidatorInterface
{
    private const API_TEXT_ATTRIBUTE_TYPE = 'text';

    public function validate(array $normalizedAttribute): array
    {
        $record = Validator::arrayToObjectRecursive($normalizedAttribute);
        $validator = new Validator();
        $validator->validate($record, $this->getJsonSchema());

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
            'required' => ['code', 'type', 'value_per_locale', 'value_per_channel'],
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
                    'type' => [ 'integer'],
                ],
                'validation_rule' => [
                    'type' => [ 'string'],
                ],
                'validation_regexp' => [
                    'type' => [ 'string'],
                ]
            ],
            'additionalProperties' => false,
        ];
    }
}
