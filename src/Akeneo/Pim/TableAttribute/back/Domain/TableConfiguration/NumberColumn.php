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

namespace Akeneo\Pim\TableAttribute\Domain\TableConfiguration;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnDataType;
use Webmozart\Assert\Assert;

class NumberColumn extends AbstractColumnDefinition
{
    private const DATATYPE = 'number';

    /**
     * @param array<string, mixed> $normalized
     */
    public static function fromNormalized(array $normalized): self
    {
        Assert::keyExists($normalized, 'code');

        return new self(
            ColumnCode::fromString($normalized['code']),
            ColumnDataType::fromString(self::DATATYPE),
            LabelCollection::fromNormalized($normalized['labels'] ?? []),
            ValidationCollection::fromNormalized($normalized['validations'] ?? [])
        );
    }
}
