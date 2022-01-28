<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value\ReferenceEntityCollectionValue;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use PhpSpec\ObjectBehavior;

class ReferenceEntityCollectionValueSpec extends ObjectBehavior {
    function let(
        RecordCode $starckCode,
        RecordCode $dysonCode
    ) {
        $this->beConstructedThrough(
            'scopableLocalizableValue',
            ['designer', [$starckCode, $dysonCode], 'ecommerce', 'en_US']
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ReferenceEntityCollectionValue::class);
        $this->shouldHaveType(ValueInterface::class);
    }

    function it_gets_a_list_of_record($starckCode, $dysonCode)
    {
        $this->getData()->shouldReturn([$starckCode, $dysonCode]);
    }

    function it_is_castable_into_a_string(RecordCode $starckCode, RecordCode $dysonCode)
    {
        $starckCode->__toString()->willReturn('starck');
        $dysonCode->__toString()->willReturn('dyson');

        $this->__toString()->shouldReturn('starck, dyson');
    }

    function it_compares_simple_reference_entity_collection_values()
    {
        $data = [RecordCode::fromString('starck'), RecordCode::fromString('corbusier')];
        $otherData = [RecordCode::fromString('starck'), RecordCode::fromString('jacobs')];

        $this->beConstructedThrough(
            'value',
            ['designer', $data, null, null]
        );
        $sameReferenceEntityCollection = ReferenceEntityCollectionValue::value('designer', $data);
        $otherReferenceEntityCollection = ReferenceEntityCollectionValue::value('designer', $otherData);
        $scopableReferenceEntityCollection = ReferenceEntityCollectionValue::scopableValue(
            'designer',
            $data,
            'ecommerce'
        );
        $localizableReferenceEntityCollection = ReferenceEntityCollectionValue::localizableValue(
            'designer',
            $data,
            'fr_FR'
        );
        $scopableLocalizableReferenceEntityCollection = ReferenceEntityCollectionValue::scopableLocalizableValue(
            'designer',
            $data,
            'ecommerce',
            'fr_FR'
        );

        $this->isEqual($sameReferenceEntityCollection)->shouldReturn(true);
        $this->isEqual($otherReferenceEntityCollection)->shouldReturn(false);
        $this->isEqual($scopableReferenceEntityCollection)->shouldReturn(false);
        $this->isEqual($localizableReferenceEntityCollection)->shouldReturn(false);
        $this->isEqual($scopableLocalizableReferenceEntityCollection)->shouldReturn(false);
    }

    function it_compares_localizable_reference_entity_collection_values()
    {
        $data = [RecordCode::fromString('starck'), RecordCode::fromString('corbusier')];
        $otherData = [RecordCode::fromString('starck'), RecordCode::fromString('jacobs')];

        $this->beConstructedThrough(
            'localizableValue',
            ['designer', $data, 'fr_FR']
        );
        $sameReferenceEntityCollection = ReferenceEntityCollectionValue::localizableValue(
            'designer',
            $data,
            'fr_FR'
        );
        $otherReferenceEntityCollection = ReferenceEntityCollectionValue::localizableValue(
            'designer',
            $otherData,
            'en_US'
        );

        $this->isEqual($sameReferenceEntityCollection)->shouldReturn(true);
        $this->isEqual($otherReferenceEntityCollection)->shouldReturn(false);
    }

    function it_compares_scopable_reference_entity_collection_values()
    {
        $data = [RecordCode::fromString('starck'), RecordCode::fromString('corbusier')];
        $otherData = [RecordCode::fromString('starck'), RecordCode::fromString('jacobs')];

        $this->beConstructedThrough(
            'scopableValue',
            ['designer', $data, 'ecommerce']
        );
        $sameReferenceEntityCollection = ReferenceEntityCollectionValue::scopableValue(
            'designer',
            $data,
            'ecommerce'
        );
        $otherReferenceEntityCollection = ReferenceEntityCollectionValue::scopableValue(
            'designer',
            $otherData,
            'ecommerce'
        );

        $this->isEqual($sameReferenceEntityCollection)->shouldReturn(true);
        $this->isEqual($otherReferenceEntityCollection)->shouldReturn(false);
    }

    function it_compares_scopable_and_localizable_reference_entity_collection_values()
    {
        $data = [RecordCode::fromString('starck'), RecordCode::fromString('corbusier')];
        $otherData = [RecordCode::fromString('starck'), RecordCode::fromString('jacobs')];

        $this->beConstructedThrough(
            'scopableLocalizableValue',
            ['designer', $data, 'ecommerce', 'fr_FR']
        );
        $sameReferenceEntityCollection = ReferenceEntityCollectionValue::scopableLocalizableValue(
            'designer',
            $data,
            'ecommerce',
            'fr_FR'
        );
        $otherReferenceEntityCollection = ReferenceEntityCollectionValue::scopableLocalizableValue(
            'designer',
            $otherData,
            'print',
            'en_US'
        );

        $this->isEqual($sameReferenceEntityCollection)->shouldReturn(true);
        $this->isEqual($otherReferenceEntityCollection)->shouldReturn(false);
    }

    function it_compares_reference_entity_code_order()
    {
        $data = [RecordCode::fromString('starck'), RecordCode::fromString('corbusier')];
        $sameDataOrder = [RecordCode::fromString('starck'), RecordCode::fromString('corbusier')];
        $otherData = [RecordCode::fromString('starck'), RecordCode::fromString('jacobs')];

        $this->beConstructedThrough(
            'scopableLocalizableValue',
            ['designer', $data, 'ecommerce', 'fr_FR']
        );

        $sameDataOrderReferenceEntityCollection = ReferenceEntityCollectionValue::scopableLocalizableValue(
            'designer',
            $sameDataOrder,
            'ecommerce',
            'fr_FR'
        );

        $otherOrderReferenceEntityCollection = ReferenceEntityCollectionValue::scopableLocalizableValue(
            'designer',
            $otherData,
            'ecommerce',
            'fr_FR'
        );

        $this->isEqual($sameDataOrderReferenceEntityCollection)->shouldReturn(true);
        $this->isEqual($otherOrderReferenceEntityCollection)->shouldReturn(false);
    }
}
