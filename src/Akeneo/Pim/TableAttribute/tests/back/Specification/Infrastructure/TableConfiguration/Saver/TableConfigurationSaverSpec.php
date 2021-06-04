<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\Saver;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Factory\ColumnFactory;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TextColumn;
use Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\Saver\TableConfigurationSaver;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TableConfigurationSaverSpec extends ObjectBehavior
{
    function let(TableConfigurationRepository $tableConfigurationRepository, ColumnFactory $columnFactory)
    {
        $this->beConstructedWith($tableConfigurationRepository, $columnFactory);
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
            ['data_type' => 'text', 'code' => 'ingredients', 'labels' => []],
            ['data_type' => 'text', 'code' => 'quantity', 'labels' => []],
        ]);
        $attribute->getCode()->willReturn('nutrition');
        $columnFactory->createFromNormalized(['data_type' => 'text', 'code' => 'ingredients', 'labels' => []])
            ->willReturn(TextColumn::fromNormalized(['data_type' => 'text', 'code' => 'ingredients', 'labels' => []]));
        $columnFactory->createFromNormalized(['data_type' => 'text', 'code' => 'quantity', 'labels' => []])
            ->willReturn(TextColumn::fromNormalized(['data_type' => 'text', 'code' => 'quantity', 'labels' => []]));

        $tableConfigurationRepository->save('nutrition', Argument::type(TableConfiguration::class))->shouldBeCalled();
        $this->save($attribute);
    }
}
