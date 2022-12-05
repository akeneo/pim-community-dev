<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Attribute\JsonSchema\Edit;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordCollectionAttribute;
use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Helper;
use Opis\JsonSchema\Validator;

class RecordAttributeValidator implements AttributeValidatorInterface
{
    public function validate(array $normalizedAttribute): array
    {
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
        return $attribute instanceof RecordAttribute || $attribute instanceof RecordCollectionAttribute;
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
                'reference_entity_code' => [
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
