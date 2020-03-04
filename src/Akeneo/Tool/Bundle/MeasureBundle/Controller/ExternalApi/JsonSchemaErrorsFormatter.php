<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Controller\ExternalApi;

/**
 * Format errors from the json-schema validator for the API connector.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class JsonSchemaErrorsFormatter
{
    public static function format(array $errors): array
    {
        return array_map(function (array $error) {
            return [
                'property' => $error['property'] ?? '',
                'message'  => $error['message'] ?? '',
            ];
        }, $errors);
    }
}
