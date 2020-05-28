<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Controller\ExternalApi\JsonSchema;

use JsonSchema\Validator;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class MeasurementFamilyListValidator
{
    public function validate(array $normalizedMeasurementFamilies): array
    {
        $validator = new Validator();
        $normalizedMeasurementFamilies = json_decode(json_encode($normalizedMeasurementFamilies));

        $validator->validate($normalizedMeasurementFamilies, $this->getJsonSchema());

        return $validator->getErrors();
    }

    private function getJsonSchema(): array
    {
        return [
            'type' => 'array',
            'items' => [
                'type' => 'object',
            ],
        ];
    }
}
