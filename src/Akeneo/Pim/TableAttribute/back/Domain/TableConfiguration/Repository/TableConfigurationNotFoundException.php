<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository;

final class TableConfigurationNotFoundException extends \RuntimeException
{
    public static function forAttributeId(int $attributeId): self
    {
        $message = sprintf(
            'Could not find table configuration for "%d" attribute id',
            $attributeId
        );

        return new self($message);
    }

    public static function forAttributeCode(string $attributeCode): self
    {
        $message = sprintf(
            'Could not find table configuration for the "%s" attribute',
            $attributeCode
        );

        return new self($message);
    }
}
