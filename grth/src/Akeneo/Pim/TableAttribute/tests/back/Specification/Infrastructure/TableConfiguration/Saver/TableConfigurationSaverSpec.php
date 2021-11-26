<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\Saver;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\BooleanColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Event\SelectOptionWasDeleted;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Factory\ColumnFactory;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\SelectOptionCollectionRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationNotFoundException;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectOptionCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfigurationUpdater;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TextColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnId;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\SelectOptionCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\WriteSelectOptionCollection;
use Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\Saver\TableConfigurationSaver;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcher;

class TableConfigurationSaverSpec extends ObjectBehavior
{
    function let(
        TableConfigurationRepository $tableConfigurationRepository,
        SelectOptionCollectionRepository $optionCollectionRepository,
        ColumnFactory $columnFactory,
        TableConfigurationUpdater $tableConfigurationUpdater,
        EventDispatcher $eventDispatcher
    ) {
        $this->beConstructedWith(
            $tableConfigurationRepository,
            $optionCollectionRepository,
            $columnFactory,
            $tableConfigurationUpdater,
            $eventDispatcher
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TableConfigurationSaver::class);
    }

    function it_is_a_saver()
    {
        $this->shouldImplement(SaverInterface::class);
    }

    function it_throws_an_exception_when_trying_to_save_anything_but_an_attribute()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('save', [new \stdClass()]);
    }

    function it_does_nothing_if_the_attribute_is_not_a_table(
        TableConfigurationRepository $tableConfigurationRepository,
        AttributeInterface $attribute
    ) {
        $attribute->getType()->willReturn(AttributeTypes::TEXT);

        $tableConfigurationRepository->save(Argument::cetera())->shouldNotBeCalled();
        $this->save($attribute);
    }

    function it_throws_an_exception_if_attribute_has_no_configuration(
        TableConfigurationRepository $tableConfigurationRepository,
        AttributeInterface $attribute
    ) {
        $attribute->getType()->willReturn(AttributeTypes::TABLE);
        $attribute->getRawTableConfiguration()->willReturn(null);

        $tableConfigurationRepository->save(Argument::cetera())->shouldNotBeCalled();
        $this->shouldThrow(\InvalidArgumentException::class)->during('save', [$attribute]);
    }

    function it_saves_a_new_table_configuration(
        TableConfigurationRepository $tableConfigurationRepository,
        ColumnFactory $columnFactory,
        AttributeInterface $attribute
    ) {
        $tableConfigurationRepository->getByAttributeCode('nutrition')
            ->willThrow(TableConfigurationNotFoundException::forAttributeCode('nutrition'));
        $tableConfigurationRepository->getNextIdentifier(ColumnCode::fromString('ingredients'))
            ->willReturn(ColumnId::fromString(ColumnIdGenerator::ingredient()));
        $tableConfigurationRepository->getNextIdentifier(ColumnCode::fromString('quantity'))
            ->willReturn(ColumnId::fromString(ColumnIdGenerator::quantity()));

        $attribute->getType()->willReturn(AttributeTypes::TABLE);
        $attribute->getRawTableConfiguration()->willReturn([
            ['data_type' => 'select', 'code' => 'ingredients', 'labels' => []],
            ['data_type' => 'text', 'code' => 'quantity', 'labels' => []],
        ]);
        $attribute->getCode()->willReturn('nutrition');
        $columnFactory->createFromNormalized(['id' => ColumnIdGenerator::ingredient(), 'data_type' => 'select', 'code' => 'ingredients', 'labels' => []])
            ->willReturn(SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'data_type' => 'text', 'code' => 'ingredients', 'labels' => []]));
        $columnFactory->createFromNormalized(['id' => ColumnIdGenerator::quantity(), 'data_type' => 'text', 'code' => 'quantity', 'labels' => []])
            ->willReturn(TextColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'data_type' => 'text', 'code' => 'quantity', 'labels' => []]));

        $tableConfigurationRepository->save('nutrition', Argument::type(TableConfiguration::class))->shouldBeCalled();
        $this->save($attribute);
    }

    function it_saves_an_existing_table_configuration_using_the_updater(
        TableConfigurationRepository $tableConfigurationRepository,
        TableConfigurationUpdater $tableConfigurationUpdater,
        AttributeInterface $attribute
    ) {
        $existingTableConfiguration = TableConfiguration::fromColumnDefinitions([
            SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient']),
            BooleanColumn::fromNormalized(['id' => ColumnIdGenerator::isAllergenic(), 'code' => 'is_allergenic']),
        ]);
        $updatedTableConfiguration = TableConfiguration::fromColumnDefinitions([
            SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient']),
            TextColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'description']),
        ]);
        $rawTableConfiguration = [
            ['data_type' => 'select', 'code' => 'ingredients', 'labels' => []],
            ['data_type' => 'text', 'code' => 'quantity', 'labels' => []],
        ];

        $attribute->getType()->willReturn(AttributeTypes::TABLE);
        $attribute->getRawTableConfiguration()->willReturn($rawTableConfiguration);
        $attribute->getCode()->willReturn('nutrition');
        $tableConfigurationRepository->getByAttributeCode('nutrition')
            ->willReturn($existingTableConfiguration);
        $tableConfigurationUpdater->update($existingTableConfiguration, $rawTableConfiguration)
            ->willReturn($updatedTableConfiguration);

        $tableConfigurationRepository->save('nutrition', $updatedTableConfiguration)->shouldBeCalled();
        $this->save($attribute);
    }

    function it_saves_select_options(
        TableConfigurationRepository $tableConfigurationRepository,
        SelectOptionCollectionRepository $optionCollectionRepository,
        ColumnFactory $columnFactory,
        EventDispatcher $eventDispatcher,
        AttributeInterface $attribute
    ) {
        $aNumberId = ColumnIdGenerator::generateAsString('a_number');
        $column1 = ['data_type' => 'select', 'code' => 'ingredients', 'labels' => [], 'options' => [['code' => 'salt', 'labels' => ['en_US' => 'Salt', 'fr_FR' => 'Sel']]]];
        $column2 = ['data_type' => 'select', 'code' => 'quantity', 'labels' => [], 'options' => [['code' => '100'], ['code' => '8000']]];
        $column3 = ['data_type' => 'number', 'code' => 'a_number', 'labels' => []];
        $column4 = ['data_type' => 'select', 'code' => 'is_allergenic', 'labels' => []];
        $column1WithId = array_merge($column1, ['id' => ColumnIdGenerator::ingredient()]);
        $column2WithId = array_merge($column2, ['id' => ColumnIdGenerator::quantity()]);
        $column3WithId = array_merge($column3, ['id' => $aNumberId]);
        $column4WithId = array_merge($column4, ['id' => ColumnIdGenerator::isAllergenic()]);
        $attribute->getType()->willReturn(AttributeTypes::TABLE);
        $attribute->getRawTableConfiguration()->willReturn([$column1, $column2, $column3, $column4]);
        $attribute->getCode()->willReturn('nutrition');
        $tableConfigurationRepository->getByAttributeCode('nutrition')
            ->willThrow(TableConfigurationNotFoundException::forAttributeCode('nutrition'));
        $tableConfigurationRepository->getNextIdentifier(ColumnCode::fromString('ingredients'))
            ->willReturn(ColumnId::fromString(ColumnIdGenerator::ingredient()));
        $tableConfigurationRepository->getNextIdentifier(ColumnCode::fromString('quantity'))
            ->willReturn(ColumnId::fromString(ColumnIdGenerator::quantity()));
        $tableConfigurationRepository->getNextIdentifier(ColumnCode::fromString('a_number'))
            ->willReturn(ColumnId::fromString($aNumberId));
        $tableConfigurationRepository->getNextIdentifier(ColumnCode::fromString('is_allergenic'))
            ->willReturn(ColumnId::fromString(ColumnIdGenerator::isAllergenic()));
        $columnFactory->createFromNormalized($column1WithId)->willReturn(SelectColumn::fromNormalized($column1WithId));
        $columnFactory->createFromNormalized($column2WithId)->willReturn(SelectColumn::fromNormalized($column2WithId));
        $columnFactory->createFromNormalized($column3WithId)->willReturn(NumberColumn::fromNormalized($column3WithId));
        $columnFactory->createFromNormalized($column4WithId)->willReturn(SelectColumn::fromNormalized($column4WithId));

        $tableConfigurationRepository->save('nutrition', Argument::type(TableConfiguration::class))->shouldBeCalled();

        $ingredientSelectOption = SelectOptionCollection::fromNormalized([['code' => 'pepper']]);
        $optionCollectionRepository->getByColumn('nutrition', ColumnCode::fromString('ingredients'))->willReturn($ingredientSelectOption);
        $optionCollectionRepository->save(
            'nutrition',
            ColumnCode::fromString('ingredients'),
            WriteSelectOptionCollection::fromReadSelectOptionCollection(SelectOptionCollection::fromNormalized($column1['options']))
        )->shouldBeCalled();
        $deletedPepperEvent = new SelectOptionWasDeleted(ColumnCode::fromString('ingredients'), SelectOptionCode::fromString('pepper'));
        $eventDispatcher->dispatch($deletedPepperEvent)->shouldBeCalled()->willReturn($deletedPepperEvent);

        $quantitySelectOption = SelectOptionCollection::fromNormalized($column2['options']);
        $optionCollectionRepository->getByColumn('nutrition', ColumnCode::fromString('quantity'))->willReturn($quantitySelectOption);
        $optionCollectionRepository->save(
            'nutrition',
            ColumnCode::fromString('quantity'),
            WriteSelectOptionCollection::fromReadSelectOptionCollection(SelectOptionCollection::fromNormalized($column2['options']))
        )->shouldBeCalled();

        $optionCollectionRepository->save('nutrition', ColumnCode::fromString('a_number'), Argument::any())->shouldNotBeCalled();
        $optionCollectionRepository->save('nutrition', ColumnCode::fromString('is_allergenic'), Argument::any())->shouldNotBeCalled();

        $this->save($attribute);
    }
}
