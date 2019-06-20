<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Connector\Api\Attribute\JsonSchema\Edit;

use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AssetAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AssetCollectionAttribute;
use Akeneo\AssetManager\Infrastructure\Connector\Api\Attribute\JsonSchema\Edit\AttributeValidatorInterface;
use JsonSchema\Validator;

class AssetAttributeValidator implements AttributeValidatorInterface
{
    public function validate(array $normalizedAttribute): array
    {
        $asset = Validator::arrayToObjectRecursive($normalizedAttribute);
        $validator = new Validator();
        $validator->validate($asset, $this->getJsonSchema());

        return $validator->getErrors();
    }

    public function support(AbstractAttribute $attribute): bool
    {
        return $attribute instanceof AssetAttribute || $attribute instanceof AssetCollectionAttribute;
    }

    private function getJsonSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['code'],
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
                '_links' => [
                    'type' => 'object'
                ],
            ],
            'additionalProperties' => false,
        ];
    }
}
