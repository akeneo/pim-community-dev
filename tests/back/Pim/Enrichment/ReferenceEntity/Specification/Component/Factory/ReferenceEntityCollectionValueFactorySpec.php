<?php

namespace Specification\Akeneo\Pim\Enrichment\ReferenceEntity\Component\Factory;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Factory\ReferenceEntityCollectionValueFactory;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value\ReferenceEntityCollectionValue;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ReferenceEntityCollectionValueFactorySpec extends ObjectBehavior {
    function let(RecordRepositoryInterface $recordRepository) {
        $this->beConstructedWith($recordRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ReferenceEntityCollectionValueFactory::class);
    }

    function it_supports_reference_entity_collection_attribute_type()
    {
        $this->supports('foo')->shouldReturn(false);
        $this->supports('akeneo_reference_entity_collection')->shouldReturn(true);
    }

    function it_creates_an_empty_reference_entity_collection_product_value(
        $recordRepository,
        AttributeInterface $attribute
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('designer');
        $attribute->getType()->willReturn('akeneo_reference_entity_collection');
        $attribute->getBackendType()->willReturn('reference_data_options');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('designer');
        $recordRepository->getByIdentifier(Argument::any())->shouldNotBeCalled();

        $productValue = $this->create(
            $attribute,
            null,
            null,
            null
        );

        $productValue->shouldReturnAnInstanceOf(ReferenceEntityCollectionValue::class);
        $productValue->shouldHaveAttribute('designer');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldBeEmpty();
    }

    function it_creates_a_localizable_and_scopable_empty_reference_entity_collection_product_value(
        $recordRepository,
        AttributeInterface $attribute
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('designer');
        $attribute->getType()->willReturn('akeneo_reference_entity_collection');
        $attribute->getBackendType()->willReturn('reference_data_options');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('designer');

        $recordRepository->getByIdentifier(Argument::any())->shouldNotBeCalled();

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            null
        );

        $productValue->shouldReturnAnInstanceOf(ReferenceEntityCollectionValue::class);
        $productValue->shouldHaveAttribute('designer');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldBeEmpty();
    }

    function it_creates_a_reference_entity_collection_product_value(
        $recordRepository,
        AttributeInterface $attribute,
        Record $starck,
        Record $dyson,
        RecordCode $starckCode,
        RecordCode $dysonCode
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('designer');
        $attribute->getType()->willReturn('akeneo_reference_entity_collection');
        $attribute->getBackendType()->willReturn('reference_data_options');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('designer');

        $designerIdentifier = ReferenceEntityIdentifier::fromString('designer');

        $recordRepository->getByReferenceEntityAndCode(
            $designerIdentifier, RecordCode::fromString('starck')
        )->willReturn($starck);

        $recordRepository->getByReferenceEntityAndCode(
            $designerIdentifier, RecordCode::fromString('dyson')
        )->willReturn($dyson);

        $starck->getCode()->willReturn($starckCode);
        $dyson->getCode()->willReturn($dysonCode);

        $productValue = $this->create(
            $attribute,
            null,
            null,
            ['starck', 'dyson']
        );

        $productValue->shouldReturnAnInstanceOf(ReferenceEntityCollectionValue::class);
        $productValue->shouldHaveAttribute('designer');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldHaveRecord([$starckCode, $dysonCode]);
    }

    function it_creates_a_localizable_and_scopable_reference_entity_collection_product_value(
        $recordRepository,
        AttributeInterface $attribute,
        Record $starck,
        Record $dyson,
        RecordCode $starckCode,
        RecordCode $dysonCode
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('designer');
        $attribute->getType()->willReturn('akeneo_reference_entity_collection');
        $attribute->getBackendType()->willReturn('reference_data_options');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('designer');

        $designerIdentifier = ReferenceEntityIdentifier::fromString('designer');

        $recordRepository->getByReferenceEntityAndCode(
            $designerIdentifier, RecordCode::fromString('starck')
        )->willReturn($starck);

        $recordRepository->getByReferenceEntityAndCode(
            $designerIdentifier, RecordCode::fromString('dyson')
        )->willReturn($dyson);

        $starck->getCode()->willReturn($starckCode);
        $dyson->getCode()->willReturn($dysonCode);

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            ['starck', 'dyson']
        );

        $productValue->shouldReturnAnInstanceOf(ReferenceEntityCollectionValue::class);
        $productValue->shouldHaveAttribute('designer');
        $productValue->shouldBeLocalizable();
        $productValue->shouldBeScopable();
        $productValue->shouldHaveRecord([$starckCode, $dysonCode]);
    }

    function it_throws_an_exception_when_provided_data_is_not_an_array(AttributeInterface $attribute)
    {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('designer');
        $attribute->getType()->willReturn('akeneo_reference_entity_collection');
        $attribute->getBackendType()->willReturn('reference_data_options');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('designer');

        $exception = InvalidPropertyTypeException::arrayExpected(
            'designer',
            ReferenceEntityCollectionValueFactory::class,
            true
        );

        $this->shouldThrow($exception)->during('create', [$attribute, null, null, true]);
    }

    function it_throws_an_exception_when_provided_data_is_not_an_array_of_string(AttributeInterface $attribute)
    {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('designer');
        $attribute->getType()->willReturn('akeneo_reference_entity_collection');
        $attribute->getBackendType()->willReturn('reference_data_options');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('designer');

        $exception = InvalidPropertyTypeException::validArrayStructureExpected(
            'designer',
            'array key "foo" expects a string as value, "array" given',
            ReferenceEntityCollectionValueFactory::class,
            ['foo' => ['bar']]
        );

        $this->shouldThrow($exception)->during('create', [$attribute, null, null, ['foo' => ['bar']]]);
    }

// TODO: To reactivate once implementation with search is made
//    function it_throws_an_exception_when_provided_data_is_not_an_existing_record_code(
//        $recordRepository,
//        AttributeInterface $attribute
//    ) {
//        $attribute->isScopable()->willReturn(false);
//        $attribute->isLocalizable()->willReturn(false);
//        $attribute->getCode()->willReturn('designer');
//        $attribute->getType()->willReturn('akeneo_reference_entity_collection');
//        $attribute->getBackendType()->willReturn('reference_data_options');
//        $attribute->isBackendTypeReferenceData()->willReturn(true);
//        $attribute->getReferenceDataName()->willReturn('designer');
//
//        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
//        $dysonIdentifier = RecordIdentifier::fromString('dyson');
//        $recordRepository->getByIdentifier($dysonIdentifier, $referenceEntityIdentifier)->willReturn(null);
//
//        $exception = InvalidPropertyException::validEntityCodeExpected(
//            'designer',
//            'record code',
//            sprintf(
//                'The code of the reference entity "%s" does not exist',
//                'designer'
//            ),
//            static::class,
//            'dyson'
//        );
//
//        $this->shouldThrow($exception)->during('create', [$attribute, null, null, ['dyson']]);
//    }

    public function getMatchers(): array
    {
        return [
            'haveAttribute'     => function ($subject, $attributeCode) {
                return $subject->getAttributeCode() === $attributeCode;
            },
            'beLocalizable'     => function ($subject) {
                return $subject->isLocalizable();
            },
            'haveLocale'        => function ($subject, $localeCode) {
                return $localeCode === $subject->getLocaleCode();
            },
            'beScopable'        => function ($subject) {
                return $subject->isScopable();
            },
            'haveChannel'       => function ($subject, $channelCode) {
                return $channelCode === $subject->getScopeCode();
            },
            'beEmpty'           => function ($subject) {
                return is_array($subject->getData()) && 0 === count($subject->getData());
            },
            'haveRecord' => function ($subject, $expected) {
                $recordIdentifiers = $subject->getData();

                return $recordIdentifiers == $expected;
            },
        ];
    }
}
