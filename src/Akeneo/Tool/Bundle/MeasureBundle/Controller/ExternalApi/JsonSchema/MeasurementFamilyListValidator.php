<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
                'type' => 'object'
            ]
        ];
    }
}
