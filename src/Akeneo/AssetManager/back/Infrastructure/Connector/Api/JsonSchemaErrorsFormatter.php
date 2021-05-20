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
        return array_map(fn(array $error) => [
            'property' => $error['property'] ?? '',
            'message'  => $error['message'] ?? '',
        ], $errors);
    }
}
