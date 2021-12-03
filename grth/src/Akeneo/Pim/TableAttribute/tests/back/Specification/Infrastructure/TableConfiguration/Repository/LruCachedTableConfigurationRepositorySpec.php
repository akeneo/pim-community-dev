<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\Repository;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\Repository\LruCachedTableConfigurationRepository;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
use PhpSpec\ObjectBehavior;

class LruCachedTableConfigurationRepositorySpec extends ObjectBehavior
{
    function let(TableConfigurationRepository $actualRepository)
    {
        $this->beConstructedWith($actualRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(LruCachedTableConfigurationRepository::class);
        $this->shouldImplement(TableConfigurationRepository::class);
    }

    function it_saves_a_table_configuration(TableConfigurationRepository $actualRepository)
    {
        $tableConfiguration = TableConfiguration::fromColumnDefinitions(
            [
                SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'labels' => ['en_US' => 'Ingredient'], 'is_required_for_completeness' => true]),
                NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity', 'labels' => ['en_US' => 'Quantity']]),
            ]
        );
        $actualRepository->save('nutrition', $tableConfiguration)->shouldBeCalled();

        $this->save('nutrition', $tableConfiguration);
    }

    function it_fetches_the_table_from_the_injected_repo_if_the_cache_is_not_hit(
        TableConfigurationRepository $actualRepository
    ) {
        $tableConfiguration = TableConfiguration::fromColumnDefinitions(
            [
                SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'labels' => ['en_US' => 'Ingredient'], 'is_required_for_completeness' => true]),
                NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity', 'labels' => ['en_US' => 'Quantity']]),
            ]
        );
        $actualRepository->getByAttributeCode('nutrition')->shouldBeCalledOnce()->willReturn($tableConfiguration);

        $this->getByAttributeCode('nutrition')->shouldReturn($tableConfiguration);
    }

    function it_fetches_the_table_configuration_from_the_cache_when_the_cache_is_hit(
        TableConfigurationRepository $actualRepository
    ) {
        $tableConfiguration = TableConfiguration::fromColumnDefinitions(
            [
                SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'labels' => ['en_US' => 'Ingredient'], 'is_required_for_completeness' => true]),
                NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity', 'labels' => ['en_US' => 'Quantity']]),
            ]
        );
        $otherTableConfiguration = TableConfiguration::fromColumnDefinitions(
            [
                SelectColumn::fromNormalized(['id' => ColumnIdGenerator::generateAsString('package'), 'code' => 'package', 'is_required_for_completeness' => true]),
                NumberColumn::fromNormalized(['id' => ColumnIdGenerator::generateAsString('width'), 'code' => 'width']),
                NumberColumn::fromNormalized(['id' => ColumnIdGenerator::generateAsString('height'), 'code' => 'height']),
                NumberColumn::fromNormalized(['id' => ColumnIdGenerator::generateAsString('depth'), 'code' => 'depth']),
            ]
        );
        $actualRepository->getByAttributeCode('nutrition')->shouldBeCalledOnce()->willReturn($tableConfiguration);
        $actualRepository->getByAttributeCode('packaging')->shouldBeCalledOnce()->willReturn($otherTableConfiguration);

        $this->getByAttributeCode('nutrition')->shouldReturn($tableConfiguration);
        $this->getByAttributeCode('packaging')->shouldReturn($otherTableConfiguration);
        $this->getByAttributeCode('nutrition')->shouldReturn($tableConfiguration);
        $this->getByAttributeCode('packaging')->shouldReturn($otherTableConfiguration);
    }

    function it_can_reset_its_cache(
        TableConfigurationRepository $actualRepository
    ) {
        $tableConfiguration = TableConfiguration::fromColumnDefinitions(
            [
                SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'labels' => ['en_US' => 'Ingredient'], 'is_required_for_completeness' => true]),
                NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity', 'labels' => ['en_US' => 'Quantity']]),
            ]
        );
        $actualRepository->getByAttributeCode('nutrition')->shouldBeCalledTimes(2)->willReturn($tableConfiguration);

        $this->getByAttributeCode('nutrition')->shouldReturn($tableConfiguration);
        $this->getByAttributeCode('nutrition')->shouldReturn($tableConfiguration);

        $this->clearCache();
        $this->getByAttributeCode('nutrition')->shouldReturn($tableConfiguration);
    }
}
