<?php

namespace Specification\Akeneo\Test\Pim\TableAttribute\Acceptance\InMemory;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Factory\TableConfigurationFactory;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\MeasurementColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ReferenceEntityColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationNotFoundException;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TextColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnId;
use Akeneo\Test\Pim\TableAttribute\Acceptance\InMemory\InMemoryTableConfigurationRepository;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
use PhpSpec\ObjectBehavior;

class InMemoryTableConfigurationRepositorySpec extends ObjectBehavior
{
    public function let(AttributeRepositoryInterface $attributeRepository)
    {
        $tableConfigurationFactory = new TableConfigurationFactory([
            'text' => 'Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TextColumn',
            'number' => 'Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn',
            'boolean' => 'Akeneo\Pim\TableAttribute\Domain\TableConfiguration\BooleanColumn',
            'select' => 'Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn',
            'reference_entity' => 'Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ReferenceEntityColumn',
            'measurement' => 'Akeneo\Pim\TableAttribute\Domain\TableConfiguration\MeasurementColumn',
        ]);
        $this->beConstructedWith($attributeRepository, $tableConfigurationFactory);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(InMemoryTableConfigurationRepository::class);
    }

    public function it_saves_a_table_attribute_configuration(
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $attribute
    ) {
        $attributeRepository->findOneByIdentifier('table_attribute_10')->shouldBeCalled()->willReturn($attribute);

        $tableConfiguration = TableConfiguration::fromColumnDefinitions([
            SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'is_required_for_completeness' => true]),
            TextColumn::fromNormalized(['id' => ColumnIdGenerator::description(), 'code' => 'description']),
            ReferenceEntityColumn::fromNormalized(['id' => ColumnIdGenerator::record(), 'code' => 'record', 'reference_entity_identifier' => 'entity']),
            MeasurementColumn::fromNormalized([
                'id' => ColumnIdGenerator::duration(),
                'code' => 'duration',
                'measurement_family_code' => 'family',
                'measurement_default_unit_code' => 'unit',
            ]),
        ]);

        $attribute->setRawTableConfiguration([
            [
                'id' => ColumnIdGenerator::ingredient(),
                'code' => 'ingredient',
                'data_type' => 'select',
                'labels' => (object) [],
                'validations' => (object) [],
                'is_required_for_completeness' => true,
            ],
            [
                'id' => ColumnIdGenerator::description(),
                'code' => 'description',
                'data_type' => 'text',
                'labels' => (object) [],
                'validations' => (object) [],
                'is_required_for_completeness' => false,
            ],
            [
                'id' => ColumnIdGenerator::record(),
                'code' => 'record',
                'data_type' => 'reference_entity',
                'labels' => (object) [],
                'validations' => (object) [],
                'is_required_for_completeness' => false,
                'reference_entity_identifier' => 'entity',
            ],
            [
                'id' => ColumnIdGenerator::duration(),
                'code' => 'duration',
                'data_type' => 'measurement',
                'labels' => (object) [],
                'validations' => (object) [],
                'is_required_for_completeness' => false,
                'measurement_family_code' => 'family',
                'measurement_default_unit_code' => 'unit',
            ],
        ])->shouldBeCalled();

        $this->save('table_attribute_10', $tableConfiguration);
    }

    public function it_gets_a_table_configuration_by_attribute_code(
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $attribute
    ) {
        $attribute->getType()->willReturn(AttributeTypes::TABLE);
        $attribute->getRawTableConfiguration()->willReturn([
            [
                'id' => ColumnIdGenerator::ingredient(),
                'code' => 'ingredient',
                'data_type' => 'select',
                'labels' => (object) [],
                'validations' => (object) [],
                'is_required_for_completeness' => true,
            ],
            [
                'id' => ColumnIdGenerator::description(),
                'code' => 'description',
                'data_type' => 'text',
                'labels' => (object) [],
                'validations' => (object) [],
                'is_required_for_completeness' => false,
            ],
            [
                'id' => ColumnIdGenerator::record(),
                'code' => 'record',
                'data_type' => 'reference_entity',
                'labels' => (object) [],
                'validations' => (object) [],
                'is_required_for_completeness' => false,
                'reference_entity_identifier' => 'entity',
            ],
            [
                'id' => ColumnIdGenerator::duration(),
                'code' => 'duration',
                'data_type' => 'measurement',
                'labels' => (object) [],
                'validations' => (object) [],
                'is_required_for_completeness' => false,
                'measurement_family_code' => 'family',
                'measurement_default_unit_code' => 'unit',
            ],
        ]);
        $attributeRepository->findOneByIdentifier('111')->shouldBeCalled()->willReturn($attribute);

        $this->getByAttributeCode('111')->shouldBeLike(TableConfiguration::fromColumnDefinitions([
            SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'is_required_for_completeness' => true]),
            TextColumn::fromNormalized(['id' => ColumnIdGenerator::description(), 'code' => 'description']),
            ReferenceEntityColumn::fromNormalized(['id' => ColumnIdGenerator::record(), 'code' => 'record', 'reference_entity_identifier' => 'entity']),
            MeasurementColumn::fromNormalized([
                'id' => ColumnIdGenerator::duration(),
                'code' => 'duration',
                'measurement_family_code' => 'family',
                'measurement_default_unit_code' => 'unit',
            ]),
        ]));
    }

    public function it_gets_the_next_identifier()
    {
        $identifier = $this->getNextIdentifier(ColumnCode::fromString('toto'));
        $identifier->shouldHaveType(ColumnId::class);
        $identifier->asString()->shouldMatch('/^toto_[0-9a-f]{8}\b-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-\b[0-9a-f]{12}$/');
    }

    public function it_throws_an_exception_when_trying_to_save_a_non_existing_attribute(
        AttributeRepositoryInterface $attributeRepository
    ) {
        $attributeRepository->findOneByIdentifier('table_attribute_11')->shouldBeCalled()->willReturn(null);
        $tableConfiguration = TableConfiguration::fromColumnDefinitions([
            SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'is_required_for_completeness' => true]),
            TextColumn::fromNormalized(['id' => ColumnIdGenerator::description(), 'code' => 'description']),
        ]);

        $this->shouldThrow(\InvalidArgumentException::class)->during('save', ['table_attribute_11', $tableConfiguration]);
    }

    public function it_throws_an_exception_when_trying_to_get_a_table_configuration_from_a_non_existing_attribute(
        AttributeRepositoryInterface $attributeRepository
    ) {
        $attributeRepository->findOneByIdentifier('table_attribute_11')->shouldBeCalled()->willReturn(null);

        $this->shouldThrow(TableConfigurationNotFoundException::class)->during('getByAttributeCode', ['table_attribute_11']);
    }

    public function it_throws_an_exception_when_trying_to_get_a_table_configuration_from_a_non_table_attribute(
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $attribute
    ) {
        $attribute->getType()->willReturn(AttributeTypes::NUMBER);
        $attributeRepository->findOneByIdentifier('table_attribute_12')->shouldBeCalled()->willReturn($attribute);

        $this->shouldThrow(TableConfigurationNotFoundException::class)->during('getByAttributeCode', ['table_attribute_12']);
    }

    public function it_throws_an_exception_when_trying_to_get_a_table_configuration_from_a_table_attribute_without_configuration(
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $attribute
    ) {
        $attribute->getType()->willReturn(AttributeTypes::TABLE);
        $attribute->getRawTableConfiguration()->shouldBeCalled()->willReturn(null);
        $attributeRepository->findOneByIdentifier('table_attribute_13')->shouldBeCalled()->willReturn($attribute);

        $this->shouldThrow(TableConfigurationNotFoundException::class)->during('getByAttributeCode', ['table_attribute_13']);
    }
}
