<?php

namespace Specification\Akeneo\Pim\TableAttribute\Domain\TableConfiguration;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ColumnDefinition;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\LabelCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ReferenceEntityColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnDataType;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnId;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\IsRequiredForCompleteness;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ReferenceEntityIdentifier;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
use PhpSpec\ObjectBehavior;

class ReferenceEntityColumnSpec extends  ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromNormalized', [
            [
                'code' => 'record',
                'labels' => ['en_US' => 'Record 1'],
                'id' => ColumnIdGenerator::record(),
                'is_required_for_completeness' => true,
                'reference_entity_identifier' => 'entity',
            ]
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ReferenceEntityColumn::class);
        $this->shouldImplement(ColumnDefinition::class);
    }

    function it_is_a_reference_entity_column()
    {
        $this->dataType()->shouldHaveType(ColumnDataType::class);
        $this->dataType()->asString()->shouldBe('reference_entity');
    }

    function it_has_a_code()
    {
        $this->code()->shouldHaveType(ColumnCode::class);
        $this->code()->asString()->shouldBe('record');
    }

    function it_has_an_id()
    {
        $this->id()->shouldHaveType(ColumnId::class);
        $this->id()->asString()->shouldBe(ColumnIdGenerator::record());
    }

    function it_has_labels()
    {
        $this->labels()->shouldHaveType(LabelCollection::class);
        $this->labels()->normalize()->shouldReturn(['en_US' => 'Record 1']);
    }

    function it_is_required_for_completeness()
    {
        $this->isRequiredForCompleteness()->shouldHaveType(IsRequiredForCompleteness::class);
        $this->isRequiredForCompleteness()->asBoolean()->shouldReturn(true);
    }

    function it_is_not_required_for_completeness()
    {
        $this->beConstructedThrough(
            'fromNormalized',
            [
                [
                    'code' => 'record',
                    'labels' => ['en_US' => 'Record 1'],
                    'id' => ColumnIdGenerator::record(),
                    'is_required_for_completeness' => false,
                    'reference_entity_identifier' => 'entity',
                ],
            ]
        );

        $this->isRequiredForCompleteness()->shouldHaveType(IsRequiredForCompleteness::class);
        $this->isRequiredForCompleteness()->asBoolean()->shouldReturn(false);
    }

    function it_has_reference_entity_identifier()
    {
        $this->referenceEntityIdentifier()->shouldHaveType(ReferenceEntityIdentifier::class);
        $this->referenceEntityIdentifier()->asString()->shouldReturn('entity');
    }

    function it_can_be_normalized()
    {
        $this->normalize()->shouldBeLike(
            [
                'data_type' => 'reference_entity',
                'code' => 'record',
                'labels' => ['en_US' => 'Record 1'],
                'id' => ColumnIdGenerator::record(),
                'is_required_for_completeness' => true,
                'reference_entity_identifier' => 'entity',
                'validations' => (object)[],
            ]
        );
    }
}
