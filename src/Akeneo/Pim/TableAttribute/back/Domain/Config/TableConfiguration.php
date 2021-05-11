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

use Webmozart\Assert\Assert;

final class TableConfiguration
{
    /** @var ColumnDefinition[] */
    private array $columnDefinitions;

    private function __construct(array $columnDefinitions)
    {
        $this->columnDefinitions = $columnDefinitions;
    }

    public static function fromColumnDefinitions(array $columnDefinitions): self
    {
        Assert::allIsInstanceOf($columnDefinitions, ColumnDefinition::class);
        Assert::minCount($columnDefinitions, 2);

        $codes = \array_map(
            fn (ColumnDefinition $definition): string => $definition->code()->asString(),
            $columnDefinitions
        );
        Assert::uniqueValues($codes);

        return new self($columnDefinitions);
    }
}
