<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Controller\ExternalApi\JsonSchema;

use JsonSchema\Validator;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MeasurementFamilyCommonStructureValidator
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
                'code' => ['type' => 'string'],
            ],
            'required' => [
                'code',
            ],
            'additionalProperties' => true,
        ];
    }
}
