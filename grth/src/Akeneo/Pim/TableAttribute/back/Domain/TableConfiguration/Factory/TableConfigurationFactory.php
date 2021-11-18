<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Factory;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ColumnDefinition;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Webmozart\Assert\Assert;

class TableConfigurationFactory
{
    /** @var array<string, string> */
    private array $columnDefinitionMapping;

    /**
     * @param array<string, string> $columnDefinitionMapping
     */
    public function __construct(array $columnDefinitionMapping)
    {
        Assert::allString(array_keys($columnDefinitionMapping));
        Assert::allClassExists($columnDefinitionMapping);
        $this->columnDefinitionMapping = $columnDefinitionMapping;
    }

    public function createFromNormalized($normalized): TableConfiguration
    {
        Assert::notEmpty($normalized);
        $normalized[\array_key_first($normalized)]['is_required_for_completeness'] = true;

        return TableConfiguration::fromColumnDefinitions(
            \array_map(
                fn (array $row): ColumnDefinition => $this->createColumnDefinitionFromNormalized(
                    $row
                ),
                $normalized
            )
        );
    }

    /**
     * @param array<string, mixed> $normalized
     */
    private function createColumnDefinitionFromNormalized(array $normalized): ColumnDefinition
    {
        Assert::keyExists($normalized, 'data_type');
        Assert::string($normalized['data_type']);

        $class = $this->columnDefinitionMapping[$normalized['data_type']] ?? null;
        if (null === $class) {
            throw new \InvalidArgumentException(sprintf('The "%s" type is unknown', $normalized['data_type']));
        }

        return $class::fromNormalized($normalized);
    }
}
