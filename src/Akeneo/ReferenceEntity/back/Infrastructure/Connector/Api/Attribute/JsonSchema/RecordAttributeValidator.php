<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Attribute\JsonSchema;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordCollectionAttribute;
use JsonSchema\Validator;

class RecordAttributeValidator implements AttributeValidatorInterface
{
    private const API_RECORD_ATTRIBUTE_TYPE = 'reference_entity_single_link';
    private const API_RECORD_COLLECTION_ATTRIBUTE_TYPE = 'reference_entity_multiple_links';

    public function validate(array $normalizedAttribute): array
    {
        $record = Validator::arrayToObjectRecursive($normalizedAttribute);
        $validator = new Validator();
        $validator->validate($record, $this->getJsonSchema());

        return $validator->getErrors();
    }

    public function forAttributeTypes(): array
    {
        return [self::API_RECORD_ATTRIBUTE_TYPE, self::API_RECORD_COLLECTION_ATTRIBUTE_TYPE];
    }

    private function getJsonSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['code', 'type', 'value_per_locale', 'value_per_channel', 'reference_entity_code'],
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
                'reference_entity_code' => [
                    'type' => [ 'string'],
                ],
            ],
            'additionalProperties' => false,
        ];
    }
}
