<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Controller\InternalApi\JsonSchema;

use JsonSchema\Validator;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MeasurementFamilyStructureValidator
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
            'type' => 'object',
            'properties' => [
                '_links' => ['type' => 'object'],
                'code' => ['type' => ['string'],],
                'labels' => [
                    'type' => 'object',
                    'patternProperties' => [
                        '.+' => ['type' => 'string'],
                    ],
                ],
                'standard_unit_code' => ['type' => 'string'],
                'units' => [
                    'type' => 'array',
                    'minItems' => 1,
                    'items' => [
                        'type' => 'object',
                        'required' => ['code', 'labels', 'convert_from_standard', 'symbol'],
                        'properties' => [
                            'code' => ['type' => 'string'],
                            'labels' => [
                                'type' => 'object',
                                'patternProperties' => [
                                    '.+' => ['type' => 'string'],
                                ],
                            ],
                            'convert_from_standard' => [
                                'minItems' => 1,
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'operator' => ['type' => 'string'],
                                        'value' => ['type' => 'string']
                                    ],
                                    'required' => ['operator', 'value'],
                                ]
                            ],
                            'symbol' => ['type' => 'string']
                        ]
                    ]
                ]
            ],
            'required' => ['code', 'labels', 'units', 'standard_unit_code'],
            'additionalProperties' => false,
        ];
    }
}
