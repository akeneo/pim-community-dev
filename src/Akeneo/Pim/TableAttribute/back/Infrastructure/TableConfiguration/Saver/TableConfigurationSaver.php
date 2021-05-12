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

namespace Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\Saver;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ColumnDefinition;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TextColumn;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Webmozart\Assert\Assert;

class TableConfigurationSaver implements SaverInterface
{
    private TableConfigurationRepository $tableConfigurationRepository;

    public function __construct(TableConfigurationRepository $tableConfigurationRepository)
    {
        $this->tableConfigurationRepository = $tableConfigurationRepository;
    }

    public function save($attribute, array $options = []): void
    {
        Assert::isInstanceOf($attribute, AttributeInterface::class);
        if (AttributeTypes::TABLE !== $attribute->getType()) {
            return;
        }
        Assert::isArray($attribute->getRawTableConfiguration());
        Assert::allIsArray($attribute->getRawTableConfiguration());

        $tableConfiguration = TableConfiguration::fromColumnDefinitions(
            array_map(
                fn (array $rawColumnDefinition): ColumnDefinition => TextColumn::fromNormalized($rawColumnDefinition),
                $attribute->getRawTableConfiguration(),
            )
        );

        $this->tableConfigurationRepository->save($attribute->getId(), $tableConfiguration);
    }
}
