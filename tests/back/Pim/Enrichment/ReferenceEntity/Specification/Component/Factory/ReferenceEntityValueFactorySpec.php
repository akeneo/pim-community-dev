<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\ReferenceEntity\Component\Factory;

use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Factory\ReferenceEntityValueFactory;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value\ReferenceEntityValue;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ReferenceEntityValueFactorySpec extends ObjectBehavior
{
    function let(RecordRepositoryInterface $recordRepository)
    {
        $this->beConstructedWith($recordRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ReferenceEntityValueFactory::class);
    }

    function it_supports_reference_entity_attribute_type()
    {
        $this->supports('akeneo_reference_entity_collection')->shouldReturn(false);
        $this->supports('akeneo_reference_entity')->shouldReturn(true);
    }

    function it_creates_a_null_reference_entity_product_value(
        RecordRepositoryInterface $recordRepository,
        AttributeInterface $attribute
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('designer');
        $attribute->getType()->willReturn('akeneo_reference_entity');
        $attribute->getBackendType()->willReturn('reference_data_option');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('designer');

        $recordRepository->getByIdentifier(Argument::any())->shouldNotBeCalled();

        $productValue = $this->create(
            $attribute,
            null,
            null,
            null
        );

        $productValue->shouldReturnAnInstanceOf(ReferenceEntityValue::class);
        $productValue->shouldHaveAttribute('designer');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldBeEmpty();
    }

    function it_creates_a_localizable_and_scopable_null_reference_entity_product_value(
        RecordRepositoryInterface $recordRepository,
        AttributeInterface $attribute
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('designer');
        $attribute->getType()->willReturn('akeneo_reference_entity');
        $attribute->getBackendType()->willReturn('reference_data_option');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('designer');

        $recordRepository->getByIdentifier(Argument::any())->shouldNotBeCalled();

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            null
        );

        $productValue->shouldReturnAnInstanceOf(ReferenceEntityValue::class);
        $productValue->shouldHaveAttribute('designer');
        $productValue->shouldBeLocalizable();
        $productValue->shouldHaveLocale('en_US');
        $productValue->shouldBeScopable();
        $productValue->shouldHaveChannel('ecommerce');
        $productValue->shouldBeEmpty();
    }

    function it_creates_a_reference_entity_product_value(
        RecordRepositoryInterface $recordRepository,
        AttributeInterface $attribute,
        Record $dyson,
        RecordCode $dysonCode
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('designer');
        $attribute->getType()->willReturn('akeneo_reference_entity');
        $attribute->getBackendType()->willReturn('reference_data_option');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('designer');

        $designerIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $recordRepository->getByReferenceEntityAndCode(
            $designerIdentifier,
            RecordCode::fromString('dyson')
        )->willReturn($dyson);
        $dyson->getCode()->willReturn($dysonCode);

        $productValue = $this->create(
            $attribute,
            null,
            null,
            'dyson'
        );

        $productValue->shouldReturnAnInstanceOf(ReferenceEntityValue::class);
        $productValue->shouldHaveAttribute('designer');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldHaveRecordCode($dysonCode);
    }

    function it_creates_a_localizable_and_scopable_reference_entity_product_value(
        RecordRepositoryInterface $recordRepository,
        AttributeInterface $attribute,
        Record $dyson,
        RecordCode $dysonCode
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('designer');
        $attribute->getType()->willReturn('akeneo_reference_entity');
        $attribute->getBackendType()->willReturn('reference_data_option');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('designer');

        $designerIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $recordRepository->getByReferenceEntityAndCode(
            $designerIdentifier,
            RecordCode::fromString('dyson')
        )->willReturn($dyson);
        $dyson->getCode()->willReturn($dysonCode);

        $productValue = $this->create(
            $attribute,
            'ecommerce',
            'en_US',
            'dyson'
        );

        $productValue->shouldReturnAnInstanceOf(ReferenceEntityValue::class);
        $productValue->shouldHaveAttribute('designer');
        $productValue->shouldBeLocalizable();
        $productValue->shouldBeScopable();
        $productValue->shouldHaveRecordCode($dysonCode);
    }

    function it_throws_an_exception_when_provided_data_is_not_a_string(AttributeInterface $attribute)
    {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('designer');
        $attribute->getType()->willReturn('akeneo_reference_entity');
        $attribute->getBackendType()->willReturn('reference_data_option');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('designer');

        $exception = InvalidPropertyTypeException::stringExpected(
            'designer',
            ReferenceEntityValueFactory::class,
            true
        );

        $this->shouldThrow($exception)->during('create', [$attribute, null, null, true]);
    }

    function it_creates_a_null_reference_entity_value_if_record_does_not_exist_anymore(
        RecordRepositoryInterface $recordRepository,
        AttributeInterface $attribute
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('designer');
        $attribute->getType()->willReturn('akeneo_reference_entity');
        $attribute->getBackendType()->willReturn('reference_data_option');
        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getReferenceDataName()->willReturn('designer');

        $designerIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $recordRepository->getByReferenceEntityAndCode(
            $designerIdentifier,
            RecordCode::fromString('dyson')
        )->willReturn(null);

        $productValue = $this->create(
            $attribute,
            null,
            null,
            null
        );

        $productValue->shouldReturnAnInstanceOf(ReferenceEntityValue::class);
        $productValue->shouldHaveAttribute('designer');
        $productValue->shouldNotBeLocalizable();
        $productValue->shouldNotBeScopable();
        $productValue->shouldBeEmpty();
    }

    public function getMatchers()
    {
        return [
            'haveAttribute' => function ($subject, $attributeCode) {
                return $subject->getAttributeCode() === $attributeCode;
            },
            'beLocalizable' => function ($subject) {
                return $subject->isLocalizable();
            },
            'haveLocale' => function ($subject, $localeCode) {
                return $localeCode === $subject->getLocaleCode();
            },
            'beScopable' => function ($subject) {
                return $subject->isScopable();
            },
            'haveChannel' => function ($subject, $channelCode) {
                return $channelCode === $subject->getScopeCode();
            },
            'beEmpty' => function ($subject) {
                return null === $subject->getData();
            },
            'haveRecordCode' => function ($subject, $expected) {
                $recordIdentifier = $subject->getData();

                return $recordIdentifier === $expected;
            },
        ];
    }
}
