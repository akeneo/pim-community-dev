<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Controller\InternalApi;

/**
 * Format errors from the json-schema validator for the API connector.
 *
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JsonSchemaErrorsFormatter
{
    public static function format(array $errors): array
    {
        return array_map(function (array $error) {
            return [
                'property' => $error['property'] ?? '',
                'message' => $error['message'] ?? '',
            ];
        }, $errors);
    }
}
