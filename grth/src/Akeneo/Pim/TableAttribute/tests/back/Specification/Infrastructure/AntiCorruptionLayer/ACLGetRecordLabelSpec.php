<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\AntiCorruptionLayer;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordLabelsByCodesInterface;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier as ForeignReferenceEntityIdentifier;
use Akeneo\Test\Pim\TableAttribute\Helper\FeatureHelper;
use PhpSpec\ObjectBehavior;

class ACLGetRecordLabelSpec extends ObjectBehavior
{
    function let($findRecordLabelsByCodes)
    {
        FeatureHelper::skipSpecTestWhenReferenceEntityIsNotActivated();

        $findRecordLabelsByCodes->beADoubleOf(FindRecordLabelsByCodesInterface::class);
    }

    function it_throws_an_exception_if_service_is_not_found()
    {
        $this->beConstructedWith(null);

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('__invoke', [ReferenceEntityIdentifier::fromString('color'), 'red', 'fr_FR']);
    }

    function it_returns_null_when_reference_entity_identifier_is_invalid($findRecordLabelsByCodes)
    {
        $this->beConstructedWith($findRecordLabelsByCodes);

        $this
            ->__invoke(ReferenceEntityIdentifier::fromString('^=?%'), 'red', 'fr_FR')
            ->shouldReturn(null);
    }

    function it_returns_null_when_record_code_is_invalid($findRecordLabelsByCodes)
    {
        $this->beConstructedWith($findRecordLabelsByCodes);

        $this
            ->__invoke(ReferenceEntityIdentifier::fromString('color'), '', 'fr_FR')
            ->shouldReturn(null);
    }

    function it_returns_label($findRecordLabelsByCodes)
    {
        $this->beConstructedWith($findRecordLabelsByCodes);
        $foreignReferenceEntityIdentifier = ForeignReferenceEntityIdentifier::fromString('color');

        $findRecordLabelsByCodes
            ->find($foreignReferenceEntityIdentifier, [RecordCode::fromString('red')])
            ->willReturn([
                'red' => LabelCollection::fromArray(['fr_FR' => 'Rouge'])
            ]);

        $this
            ->__invoke(ReferenceEntityIdentifier::fromString('color'), 'red', 'fr_FR')
            ->shouldReturn('Rouge');
    }

    function it_returns_null_if_label_does_not_exist($findRecordLabelsByCodes)
    {
        $this->beConstructedWith($findRecordLabelsByCodes);
        $foreignReferenceEntityIdentifier = ForeignReferenceEntityIdentifier::fromString('color');

        $findRecordLabelsByCodes
            ->find($foreignReferenceEntityIdentifier, [RecordCode::fromString('red')])
            ->willReturn([
                'red' => LabelCollection::fromArray(['en_US' => 'Red'])
            ]);

        $this
            ->__invoke(ReferenceEntityIdentifier::fromString('color'), 'red', 'fr_FR')
            ->shouldReturn(null);
    }

    function it_returns_null_if_record_does_not_exist($findRecordLabelsByCodes)
    {
        $this->beConstructedWith($findRecordLabelsByCodes);
        $foreignReferenceEntityIdentifier = ForeignReferenceEntityIdentifier::fromString('color');

        $findRecordLabelsByCodes
            ->find($foreignReferenceEntityIdentifier, [RecordCode::fromString('red')])
            ->willReturn([]);

        $this
            ->__invoke(ReferenceEntityIdentifier::fromString('color'), 'red', 'fr_FR')
            ->shouldReturn(null);
    }
}
