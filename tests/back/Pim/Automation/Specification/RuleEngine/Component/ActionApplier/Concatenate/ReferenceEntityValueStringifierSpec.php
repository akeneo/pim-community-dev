<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate;

use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate\ReferenceEntityValueStringifier;
use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate\ValueStringifierInterface;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value\ReferenceEntityCollectionValue;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value\ReferenceEntityValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordDetailsInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordDetails;
use PhpSpec\ObjectBehavior;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class ReferenceEntityValueStringifierSpec extends ObjectBehavior
{
    function let(FindRecordDetailsInterface $findRecordDetails, GetAttributes $getAttributes)
    {
        $this->beConstructedWith($findRecordDetails, $getAttributes, ['akeneo_reference_entity', 'akeneo_reference_entity_collection']);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(ReferenceEntityValueStringifier::class);
    }

    function it_implements_value_stringifier_interface()
    {
        $this->shouldBeAnInstanceOf(ValueStringifierInterface::class);
    }

    function it_returns_supported_attribute_types()
    {
        $this->forAttributesTypes()->shouldBe(['akeneo_reference_entity', 'akeneo_reference_entity_collection']);
    }

    function it_stringifies_a_reference_entity_value_with_label_locale(
        FindRecordDetailsInterface $findRecordDetails,
        GetAttributes $getAttributes
    ) {
        $value = ReferenceEntityValue::value('attribute_code', RecordCode::fromString('record_code'));
        $attribute = $this->buildAttribute('code', 'reference_data');
        $recordDetails = $this->buildRecordDetails('reference_data', 'record_code', [
            'fr_FR' => 'la valeur',
            'en_US' => 'the value',
        ]);

        $getAttributes->forCode('attribute_code')->willReturn($attribute);
        $findRecordDetails->find(
            ReferenceEntityIdentifier::fromString('reference_data'),
            RecordCode::fromString('record_code')
        )->willReturn($recordDetails);

        $this->stringify($value, ['label_locale' => 'en_US'])
            ->shouldReturn('the value');
    }

    function it_stringifies_a_reference_entity_collection_value_with_label_locale(
        FindRecordDetailsInterface $findRecordDetails,
        GetAttributes $getAttributes
    ) {
        $value = ReferenceEntityCollectionValue::value('attribute_code', [
            RecordCode::fromString('s'),
            RecordCode::fromString('m'),
        ]);
        $attribute = $this->buildAttribute('code', 'size');
        $recordDetails1 = $this->buildRecordDetails('size', 's', [
            'fr_FR' => 'petit',
            'en_US' => 'small',
        ]);
        $recordDetails2 = $this->buildRecordDetails('size', 'm', [
            'fr_FR' => 'moyen',
            'en_US' => 'medium',
        ]);

        $getAttributes->forCode('attribute_code')->willReturn($attribute);
        $findRecordDetails->find(
            ReferenceEntityIdentifier::fromString('size'),
            RecordCode::fromString('s')
        )->willReturn($recordDetails1);
        $findRecordDetails->find(
            ReferenceEntityIdentifier::fromString('size'),
            RecordCode::fromString('m')
        )->willReturn($recordDetails2);

        $this->stringify($value, ['label_locale' => 'en_US'])
            ->shouldReturn('small, medium');
    }

    function it_stringifies_a_reference_entity_value_with_unknown_label_locale(
        FindRecordDetailsInterface $findRecordDetails,
        GetAttributes $getAttributes
    ) {
        $value = ReferenceEntityValue::value('attribute_code', RecordCode::fromString('record_code'));
        $attribute = $this->buildAttribute('code', 'reference_data');
        $recordDetails = $this->buildRecordDetails('reference_data', 'record_code', [
            'fr_FR' => 'la valeur',
            'en_US' => 'the value',
        ]);

        $getAttributes->forCode('attribute_code')->willReturn($attribute);
        $findRecordDetails->find(
            ReferenceEntityIdentifier::fromString('reference_data'),
            RecordCode::fromString('record_code')
        )->willReturn($recordDetails);

        $this->stringify($value, ['label_locale' => 'unknown'])
            ->shouldReturn('record_code');
    }

    function it_stringifies_a_reference_entity_collection_value_with_unknown_label_locale(
        FindRecordDetailsInterface $findRecordDetails,
        GetAttributes $getAttributes
    ) {
        $value = ReferenceEntityCollectionValue::value('attribute_code', [
            RecordCode::fromString('s'),
            RecordCode::fromString('m'),
        ]);
        $attribute = $this->buildAttribute('code', 'size');
        $recordDetails1 = $this->buildRecordDetails('size', 's', [
            'fr_FR' => 'petit',
            'en_US' => 'small',
        ]);
        $recordDetails2 = $this->buildRecordDetails('size', 'm', [
            'fr_FR' => 'moyen',
            'en_US' => 'medium',
        ]);

        $getAttributes->forCode('attribute_code')->willReturn($attribute);
        $findRecordDetails->find(
            ReferenceEntityIdentifier::fromString('size'),
            RecordCode::fromString('s')
        )->willReturn($recordDetails1);
        $findRecordDetails->find(
            ReferenceEntityIdentifier::fromString('size'),
            RecordCode::fromString('m')
        )->willReturn($recordDetails2);

        $this->stringify($value, ['label_locale' => 'de_DE'])
            ->shouldReturn('s, m');
    }

    function it_stringifies_a_reference_entity_value_without_label_locale(
        FindRecordDetailsInterface $findRecordDetails,
        GetAttributes $getAttributes
    ) {
        $value = ReferenceEntityValue::value('attribute_code', RecordCode::fromString('s'));

        $attribute = $this->buildAttribute('code', 'size');
        $getAttributes->forCode('attribute_code')->willReturn($attribute);

        $recordDetails1 = $this->buildRecordDetails('size', 's', [
            'fr_FR' => 'petit',
            'en_US' => 'small',
        ]);
        $findRecordDetails->find(
            ReferenceEntityIdentifier::fromString('size'),
            RecordCode::fromString('s')
        )->willReturn($recordDetails1);

        $this->stringify($value, [])
            ->shouldReturn('s');
    }

    function it_stringifies_a_reference_entity_collection_value_without_label_locale(
        FindRecordDetailsInterface $findRecordDetails,
        GetAttributes $getAttributes
    ) {
        $value = ReferenceEntityCollectionValue::value('attribute_code', [
            RecordCode::fromString('s'),
            RecordCode::fromString('m'),
        ]);
        $attribute = $this->buildAttribute('code', 'size');
        $recordDetails1 = $this->buildRecordDetails('size', 's', [
            'fr_FR' => 'petit',
            'en_US' => 'small',
        ]);
        $recordDetails2 = $this->buildRecordDetails('size', 'm', [
            'fr_FR' => 'moyen',
            'en_US' => 'medium',
        ]);

        $getAttributes->forCode('attribute_code')->willReturn($attribute);
        $findRecordDetails->find(
            ReferenceEntityIdentifier::fromString('size'),
            RecordCode::fromString('s')
        )->willReturn($recordDetails1);
        $findRecordDetails->find(
            ReferenceEntityIdentifier::fromString('size'),
            RecordCode::fromString('m')
        )->willReturn($recordDetails2);

        $this->stringify($value, [])
            ->shouldReturn('s, m');
    }

    function it_stringifies_a_reference_entity_value_with_unknown_attribute(
        GetAttributes $getAttributes
    ) {
        $value = ReferenceEntityValue::value('attribute_code', RecordCode::fromString('record_code'));
        $getAttributes->forCode('attribute_code')->willreturn(null);

        $this->stringify($value, ['label_locale' => 'en_US'])
            ->shouldReturn('');
    }

    function it_stringifies_a_reference_entity_collection_value_with_unknown_attribute(
        GetAttributes $getAttributes
    ) {
        $value = ReferenceEntityCollectionValue::value('attribute_code', [
            RecordCode::fromString('s'),
            RecordCode::fromString('m'),
        ]);
        $getAttributes->forCode('attribute_code')->willReturn(null);

        $this->stringify($value, ['label_locale' => 'en_US'])
            ->shouldReturn('');
    }

    function it_stringifies_a_reference_entity_value_with_unknown_record(
        FindRecordDetailsInterface $findRecordDetails,
        GetAttributes $getAttributes
    ) {
        $value = ReferenceEntityValue::value('attribute_code', RecordCode::fromString('record_code'));
        $attribute = $this->buildAttribute('code', 'reference_data');

        $getAttributes->forCode('attribute_code')->willReturn($attribute);
        $findRecordDetails->find(
            ReferenceEntityIdentifier::fromString('reference_data'),
            RecordCode::fromString('record_code')
        )->willReturn(null);

        $this->stringify($value, ['label_locale' => 'en_US'])
            ->shouldReturn('');
    }

    function it_stringifies_a_reference_entity_collection_value_with_unknown_record(
        FindRecordDetailsInterface $findRecordDetails,
        GetAttributes $getAttributes
    ) {
        $value = ReferenceEntityCollectionValue::value('attribute_code', [
            RecordCode::fromString('s'),
            RecordCode::fromString('m'),
        ]);
        $attribute = $this->buildAttribute('code', 'size');
        $recordDetails1 = $this->buildRecordDetails('size', 's', [
            'fr_FR' => 'petit',
            'en_US' => 'small',
        ]);

        $getAttributes->forCode('attribute_code')->willReturn($attribute);
        $findRecordDetails->find(
            ReferenceEntityIdentifier::fromString('size'),
            RecordCode::fromString('s')
        )->willReturn($recordDetails1);
        $findRecordDetails->find(
            ReferenceEntityIdentifier::fromString('size'),
            RecordCode::fromString('m')
        )->willReturn(null);

        $this->stringify($value, ['label_locale' => 'en_US'])
            ->shouldReturn('small');
    }

    private function buildRecordDetails(string $refEntityIdentifier, string $recordCode, array $labels): RecordDetails
    {
        return new RecordDetails(
            RecordIdentifier::fromString(uniqid()),
            ReferenceEntityIdentifier::fromString($refEntityIdentifier),
            RecordCode::fromString($recordCode),
            LabelCollection::fromArray($labels),
            \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, '2020-06-23T09:24:03-07:00'),
            \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, '2020-06-23T09:30:03-07:00'),
            Image::createEmpty(),
            [],
            true
        );
    }

    private function buildAttribute(string $code, string $referenceDataName): Attribute
    {
        return new Attribute(
            $code,
            AttributeTypes::BACKEND_TYPE_REF_DATA_OPTION,
            ['reference_data_name' => $referenceDataName],
            false,
            false,
            null,
            null,
            true,
            '',
            []
        );
    }
}
