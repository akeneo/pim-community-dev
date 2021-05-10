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

namespace Akeneo\Pim\TableAttribute\Domain\Config;

use Akeneo\Pim\TableAttribute\Domain\Config\ValueObject\ColumnCode;
use Webmozart\Assert\Assert;

class TextColumn extends ColumnDefinition
{
    private const DATATYPE = 'text';

    // validation for text (min, max chars)

    public static function fromNormalized(array $normalized): self
    {
        Assert::keyExists($normalized, 'code');

        $labels = $normalized['labels'] ?? [];
        Assert::isArray($labels);

        return new self(ColumnCode::fromString($normalized['code']), self::DATATYPE, $labels);
    }
}
