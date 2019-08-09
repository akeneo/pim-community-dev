<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Connector\Api\Attribute\JsonSchema\Create;

use Akeneo\AssetManager\Domain\Model\Attribute\AssetAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AssetCollectionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\ImageAttribute;
use Akeneo\AssetManager\Infrastructure\Connector\Api\Attribute\JsonSchema\Create\AttributeValidatorInterface;
use JsonSchema\Validator;

class AssetAttributeValidator implements AttributeValidatorInterface
{
    private const API_ASSET_ATTRIBUTE_TYPE = 'asset_family_single_link';
    private const API_ASSET_COLLECTION_ATTRIBUTE_TYPE = 'asset_family_multiple_links';

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
        return [self::API_ASSET_ATTRIBUTE_TYPE, self::API_ASSET_COLLECTION_ATTRIBUTE_TYPE];
    }

    private function getJsonSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['code', 'type', 'asset_family_code'],
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
                'asset_family_code' => [
                    'type' => [ 'string'],
                ],
            ],
            'additionalProperties' => false,
        ];
    }
}
