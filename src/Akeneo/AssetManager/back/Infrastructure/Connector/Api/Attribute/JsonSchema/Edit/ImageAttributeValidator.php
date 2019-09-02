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
use Akeneo\AssetManager\Domain\Model\Attribute\ImageAttribute;
use JsonSchema\Validator;

class ImageAttributeValidator implements AttributeValidatorInterface
{
    public function validate(array $normalizedAttribute): array
    {
        $normalizedAttribute['labels'] =  empty($normalizedAttribute['labels']) ? (object) [] : $normalizedAttribute['labels'] ;
        $asset = Validator::arrayToObjectRecursive($normalizedAttribute);
        $validator = new Validator();
        $validator->validate($asset, $this->getJsonSchema());

        return $validator->getErrors();
    }

    public function support(AbstractAttribute $attribute): bool
    {
        return $attribute instanceof ImageAttribute;
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
                'allowed_extensions' => [
                    'type' => ['array'],
                    'items' => [
                        'type' => 'string',
                    ]
                ],
                'max_file_size' => [
                    'type' => [ 'string', 'null'],
                ],
                '_links' => [
                    'type' => 'object'
                ],
            ],
            'additionalProperties' => false,
        ];
    }
}
