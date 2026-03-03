<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Controller\ExternalApi\JsonSchema;

use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Helper;
use Opis\JsonSchema\Validator;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MeasurementFamilyCommonStructureValidator
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
