<?php

namespace spec\Akeneo\Pim\EnrichedEntity\Component\Factory;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\Pim\EnrichedEntity\Component\Factory\EnrichedEntityCollectionValueFactory;
use Akeneo\Pim\EnrichedEntity\Component\Value\EnrichedEntityCollectionValue;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EnrichedEntityCollectionValueFactorySpec extends ObjectBehavior {
    function let(RecordRepositoryInterface $recordRepository) {
        $this->beConstructedWith($recordRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(EnrichedEntityCollectionValueFactory::class);
    }

    function it_supports_enriched_entity_collection_attribute_type()
    {
        $this->supports('foo')->shouldReturn(false);
        $this->supports('akeneo_enriched_entity_collection')->shouldReturn(true);
    }

    function it_creates_an_empty_enriched_entity_collection_product_value(
        $recordRepository,
        AttributeInterface $attribute
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('designer');
        $attribute->getType()->willReturn('akeneo_enriched_entity_collection');
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

        $productValue->shouldReturnAnInstanceOf(EnrichedEntityCollectionValue::class);
        $productValue->shouldHaveAttribute('designer');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldBeEmpty();
    }

    function it_creates_a_localizable_and_scopable_empty_enriched_entity_collection_product_value(
        $recordRepository,
        AttributeInterface $attribute
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('designer');
        $attribute->getType()->willReturn('akeneo_enriched_entity_collection');
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

        $productValue->shouldReturnAnInstanceOf(EnrichedEntityCollectionValue::class);
        $productValue->shouldHaveAttribute('designer');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldBeEmpty();
    }

    function it_creates_an_enriched_entity_collection_product_value(
        $recordRepository,
        AttributeInterface $attribute,
        Record $starck,
        Record $dyson
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('designer');
        $attribute->getType()->willReturn('akeneo_enriched_entity_collection');
        $attribute->getBackendType()->willReturn('reference_data_options');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('designer');

        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $starckIdentifier = RecordIdentifier::fromString('designer', 'starck');
        $recordRepository->getByIdentifier($starckIdentifier, $enrichedEntityIdentifier)->willReturn($starck);
        $dysonIdentifier = RecordIdentifier::fromString('designer', 'dyson');
        $recordRepository->getByIdentifier($dysonIdentifier, $enrichedEntityIdentifier)->willReturn($dyson);

        $productValue = $this->create(
            $attribute,
            null,
            null,
            ['starck', 'dyson']
        );

        $productValue->shouldReturnAnInstanceOf(EnrichedEntityCollectionValue::class);
        $productValue->shouldHaveAttribute('designer');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldHaveRecord([$starck, $dyson]);
    }

    function it_creates_a_localizable_and_scopable_enriched_entity_collection_product_value(
        $recordRepository,
        AttributeInterface $attribute,
        Record $starck,
        Record $dyson
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('designer');
        $attribute->getType()->willReturn('akeneo_enriched_entity_collection');
        $attribute->getBackendType()->willReturn('reference_data_options');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('designer');

        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $starckIdentifier = RecordIdentifier::fromString('starck', '');
        $recordRepository->getByIdentifier($starckIdentifier, $enrichedEntityIdentifier)->willReturn($starck);
        $dysonIdentifier = RecordIdentifier::fromString('dyson', '');
        $recordRepository->getByIdentifier($dysonIdentifier, $enrichedEntityIdentifier)->willReturn($dyson);

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            ['starck', 'dyson']
        );

        $productValue->shouldReturnAnInstanceOf(EnrichedEntityCollectionValue::class);
        $productValue->shouldHaveAttribute('designer');
        $productValue->shouldBeLocalizable();
        $productValue->shouldBeScopable();
        $productValue->shouldHaveRecord([$starck, $dyson]);
    }

    function it_throws_an_exception_when_provided_data_is_not_an_array(AttributeInterface $attribute)
    {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('designer');
        $attribute->getType()->willReturn('akeneo_enriched_entity_collection');
        $attribute->getBackendType()->willReturn('reference_data_options');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('designer');

        $exception = InvalidPropertyTypeException::arrayExpected(
            'designer',
            EnrichedEntityCollectionValueFactory::class,
            true
        );

        $this->shouldThrow($exception)->during('create', [$attribute, null, null, true]);
    }

    function it_throws_an_exception_when_provided_data_is_not_an_array_of_string(AttributeInterface $attribute)
    {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('designer');
        $attribute->getType()->willReturn('akeneo_enriched_entity_collection');
        $attribute->getBackendType()->willReturn('reference_data_options');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('designer');

        $exception = InvalidPropertyTypeException::validArrayStructureExpected(
            'designer',
            'array key "foo" expects a string as value, "array" given',
            EnrichedEntityCollectionValueFactory::class,
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
//        $attribute->getType()->willReturn('akeneo_enriched_entity_collection');
//        $attribute->getBackendType()->willReturn('reference_data_options');
//        $attribute->isBackendTypeReferenceData()->willReturn(true);
//        $attribute->getReferenceDataName()->willReturn('designer');
//
//        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
//        $dysonIdentifier = RecordIdentifier::fromString('dyson');
//        $recordRepository->getByIdentifier($dysonIdentifier, $enrichedEntityIdentifier)->willReturn(null);
//
//        $exception = InvalidPropertyException::validEntityCodeExpected(
//            'designer',
//            'record code',
//            sprintf(
//                'The code of the enriched entity "%s" does not exist',
//                'designer'
//            ),
//            static::class,
//            'dyson'
//        );
//
//        $this->shouldThrow($exception)->during('create', [$attribute, null, null, ['dyson']]);
//    }

    public function getMatchers()
    {
        return [
            'haveAttribute'     => function ($subject, $attributeCode) {
                return $subject->getAttribute()->getCode() === $attributeCode;
            },
            'beLocalizable'     => function ($subject) {
                return null !== $subject->getLocale();
            },
            'haveLocale'        => function ($subject, $localeCode) {
                return $localeCode === $subject->getLocale();
            },
            'beScopable'        => function ($subject) {
                return null !== $subject->getScope();
            },
            'haveChannel'       => function ($subject, $channelCode) {
                return $channelCode === $subject->getScope();
            },
            'beEmpty'           => function ($subject) {
                return is_array($subject->getData()) && 0 === count($subject->getData());
            },
            'haveRecord' => function ($subject, $expected) {
                $records = $subject->getData();

                $hasRecords = false;
                foreach ($records as $record) {
                    $hasRecords = in_array($record, $expected);
                }

                return $hasRecords;
            },
        ];
    }
}
