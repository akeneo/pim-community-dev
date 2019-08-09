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

use Akeneo\AssetManager\Infrastructure\Connector\Api\Attribute\JsonSchema\Create\AttributeValidatorInterface;
use JsonSchema\Validator;

class MediaLinkAttributeValidator implements AttributeValidatorInterface
{
    private const API_URL_ATTRIBUTE_TYPE = 'media_link';

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
