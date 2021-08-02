<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\Saver;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Factory\ColumnFactory;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\SelectOptionCollectionRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectOptionCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TextColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\Saver\TableConfigurationSaver;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TableConfigurationSaverSpec extends ObjectBehavior
{
    function let(
        TableConfigurationRepository $tableConfigurationRepository,
        SelectOptionCollectionRepository $optionCollectionRepository,
        ColumnFactory $columnFactory
    ) {
        $this->beConstructedWith($tableConfigurationRepository, $optionCollectionRepository, $columnFactory);
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

    function it_saves_a_table_configuration(
        TableConfigurationRepository $tableConfigurationRepository,
        ColumnFactory $columnFactory,
        AttributeInterface $attribute
    ) {
        $attribute->getType()->willReturn(AttributeTypes::TABLE);
        $attribute->getRawTableConfiguration()->willReturn([
            ['data_type' => 'select', 'code' => 'ingredients', 'labels' => []],
            ['data_type' => 'text', 'code' => 'quantity', 'labels' => []],
        ]);
        $attribute->getCode()->willReturn('nutrition');
        $columnFactory->createFromNormalized(['data_type' => 'select', 'code' => 'ingredients', 'labels' => []])
            ->willReturn(SelectColumn::fromNormalized(['data_type' => 'text', 'code' => 'ingredients', 'labels' => []]));
        $columnFactory->createFromNormalized(['data_type' => 'text', 'code' => 'quantity', 'labels' => []])
            ->willReturn(TextColumn::fromNormalized(['data_type' => 'text', 'code' => 'quantity', 'labels' => []]));

        $tableConfigurationRepository->save('nutrition', Argument::type(TableConfiguration::class))->shouldBeCalled();
        $this->save($attribute);
    }

    function it_saves_select_options(
        TableConfigurationRepository $tableConfigurationRepository,
        SelectOptionCollectionRepository $optionCollectionRepository,
        ColumnFactory $columnFactory,
        AttributeInterface $attribute
    ) {
        $column1 = ['data_type' => 'select', 'code' => 'ingredients', 'labels' => [], 'options' => [['code' => 'salt', 'labels' => ['en_US' => 'Salt', 'fr_FR' => 'Sel']]]];
        $column2 = ['data_type' => 'select', 'code' => 'quantity', 'labels' => [], 'options' => [['code' => '100'], ['code' => '8000']]];
        $column3 = ['data_type' => 'number', 'code' => 'a_number', 'labels' => []];
        $column4 = ['data_type' => 'select', 'code' => 'is_allergenic', 'labels' => []];
        $attribute->getType()->willReturn(AttributeTypes::TABLE);
        $attribute->getRawTableConfiguration()->willReturn([$column1, $column2, $column3, $column4]);
        $attribute->getCode()->willReturn('nutrition');
        $columnFactory->createFromNormalized($column1)->willReturn(SelectColumn::fromNormalized($column1));
        $columnFactory->createFromNormalized($column2)->willReturn(SelectColumn::fromNormalized($column2));
        $columnFactory->createFromNormalized($column3)->willReturn(NumberColumn::fromNormalized($column3));
        $columnFactory->createFromNormalized($column4)->willReturn(SelectColumn::fromNormalized($column4));

        $tableConfigurationRepository->save('nutrition', Argument::type(TableConfiguration::class))->shouldBeCalled();

        $optionCollectionRepository->save('nutrition', ColumnCode::fromString('ingredients'), SelectOptionCollection::fromNormalized($column1['options']))->shouldBeCalled();
        $optionCollectionRepository->save('nutrition', ColumnCode::fromString('quantity'), SelectOptionCollection::fromNormalized($column2['options']))->shouldBeCalled();
        $optionCollectionRepository->save('nutrition', ColumnCode::fromString('a_number'), Argument::any())->shouldNotBeCalled();
        $optionCollectionRepository->save('nutrition', ColumnCode::fromString('is_allergenic'), Argument::any())->shouldNotBeCalled();

        $this->save($attribute);
    }
}
