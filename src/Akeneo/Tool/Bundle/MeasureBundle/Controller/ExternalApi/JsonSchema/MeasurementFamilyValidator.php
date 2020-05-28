<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Controller\ExternalApi\JsonSchema;

use JsonSchema\Validator;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class MeasurementFamilyValidator
{
    public function validate(array $normalizedMeasurementFamily): array
    {
        $validator = new Validator();
        $normalizedMeasurementFamilyObject = Validator::arrayToObjectRecursive($normalizedMeasurementFamily);
        $validator->validate($normalizedMeasurementFamilyObject, $this->getJsonSchema());

        return $validator->getErrors();
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
