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

namespace Akeneo\AssetManager\Infrastructure\Connector\Api;

/**
 * Format errors from the json-schema validator for the API connector.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class JsonSchemaErrorsFormatter
{
    public static function format(array $errors): array
    {
        return array_map(fn (array $error) => [
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
