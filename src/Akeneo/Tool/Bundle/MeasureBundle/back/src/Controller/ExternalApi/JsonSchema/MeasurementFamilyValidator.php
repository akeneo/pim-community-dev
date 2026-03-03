<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Controller\ExternalApi\JsonSchema;

use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Helper;
use Opis\JsonSchema\Validator;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class MeasurementFamilyValidator
{
    public function validate(array $normalizedMeasurementFamily): array
    {
        $validator = new Validator();
        $validator->setMaxErrors(50);

        $result = $validator->validate(
            Helper::toJSON($normalizedMeasurementFamily),
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

    private function getJsonSchema(): array
    {
        return [
            'type'                 => 'object',
            'properties'           => [
                '_links'             => ['type' => 'object'],
                'code'               => ['type' => 'string'],
                'labels'             => [
                    'type'              => ['object', 'array'],
                    'patternProperties' => [
                        '.+' => ['type' => 'string'],
                    ],
                ],
                'standard_unit_code' => ['type' => 'string'],
                'units'              => [
                    'type'  => 'object',
                    'patternProperties' => [
                        '.+' => [
                            'type'       => 'object',
                            'properties' => [
                                'code'                  => ['type' => 'string'],
                                'labels'                => [
                                    'type'              => ['object', 'array'],
                                    'patternProperties' => [
                                        '.+' => ['type' => 'string'],
                                    ],
                                ],
                                'convert_from_standard' => [
                                    'type'  => 'array',
                                    'items' => [
                                        'type'       => 'object',
                                        'properties' => [
                                            'operator' => ['type' => 'string'],
                                            'value'    => ['type' => 'string']
                                        ],
                                        'required'   => ['operator', 'value'],
                                    ]
                                ],
                                'symbol'                => ['type' => 'string']
                            ],
                            'required' => ['code', 'convert_from_standard'],
                            'additionalProperties' => false,
                        ],
                    ]
                ]
            ],
            'required'             => ['code', 'units', 'standard_unit_code'],
            'additionalProperties' => false,
        ];
    }
}
