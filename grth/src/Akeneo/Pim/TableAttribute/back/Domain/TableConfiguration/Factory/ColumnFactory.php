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

namespace Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Factory;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ColumnDefinition;
use Webmozart\Assert\Assert;

class ColumnFactory
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

    /**
     * @param array<string, mixed> $normalized
     */
    public function createFromNormalized(array $normalized): ColumnDefinition
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
