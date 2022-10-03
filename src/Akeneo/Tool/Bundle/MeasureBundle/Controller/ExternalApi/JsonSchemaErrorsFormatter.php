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
        return array_map(static fn (array $error) => [
            'property' => isset($error['property']) ? self::convertOpisPropertyPath($error['property']) : '',
            'message'  => $error['message'] ?? '',
        ], $errors);
    }

    private static function convertOpisPropertyPath(string $opisPropertyPath): string
    {
        if ($opisPropertyPath === '/') {
            return '';
        }

        $explodedOpisPropertyPath = explode('/', ltrim($opisPropertyPath, '/'));
        $explodedAkeneoPropertyPath = array_map(
            static fn ($propertyPath) => sprintf(is_numeric($propertyPath) ? '[%d]' : '.%s', $propertyPath),
            $explodedOpisPropertyPath
        );

        return ltrim(implode('', $explodedAkeneoPropertyPath), '.');
    }
}
