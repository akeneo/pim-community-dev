<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Tool\Component\Connector\Exception;

class InvalidYamlFileException extends \Exception
{
    public static function doesNotContainRootLevel(string $expectedRootLevel): self
    {
        return new self(
            sprintf(
                'File do not respect the expected structure: missing root level "%s"',
                $expectedRootLevel
            )
        );
    }

    public static function rowShouldBeAnArray(mixed $key, mixed $value): self
    {
        return new self(
            sprintf(
                'File do not respect the expected structure: Value at "%s" should be an array, actual value is "%s"',
                $key,
                $value,
            ),
        );
    }
}
