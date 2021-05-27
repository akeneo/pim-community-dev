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

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class ValuesDecoder
{
    public static function decode(string $values): array
    {
        $decodedValues = json_decode($values, true);

        if (null === $decodedValues) {
            $message = sprintf('Impossible to decode asset values %s', $values);
            throw new \RuntimeException($message);
        }

        return array_map(function ($value) {
            $value['data'] = self::sanitizeData($value['data']);

            return $value;
        }, $decodedValues);
    }

    /**
     * @param mixed $data
     *
     * @return mixed
     */
    private static function sanitizeData($data)
    {
        if (!is_string($data)) {
            return $data;
        }

        return preg_replace('/<[^>]*>/', '', html_entity_decode(str_replace(["\r", "\n"], ' ', $data)));
    }
}
