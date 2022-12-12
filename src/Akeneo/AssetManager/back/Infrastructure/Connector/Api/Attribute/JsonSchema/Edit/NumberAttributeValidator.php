<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Connector\Api\Attribute\JsonSchema\Edit;

use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\NumberAttribute;
use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Helper;
use Opis\JsonSchema\Validator;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class NumberAttributeValidator implements AttributeValidatorInterface
{
    public function validate(array $normalizedAttribute): array
    {
        $normalizedAttribute['labels'] =  empty($normalizedAttribute['labels']) ? (object) [] : $normalizedAttribute['labels'] ;
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

    public function support(AbstractAttribute $attribute): bool
    {
        return $attribute instanceof NumberAttribute;
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
                'is_read_only' => [
                    'type' => [ 'boolean'],
                ],
                'decimals_allowed' => [
                    'type' => [ 'boolean'],
                ],
                'min_value' => [
                    'type' => [ 'string', 'integer', 'null'],
                ],
                'max_value' => [
                    'type' => [ 'string', 'integer', 'null'],
                ],
                '_links' => [
                    'type' => 'object'
                ],
            ],
            'additionalProperties' => false,
        ];
    }
}
