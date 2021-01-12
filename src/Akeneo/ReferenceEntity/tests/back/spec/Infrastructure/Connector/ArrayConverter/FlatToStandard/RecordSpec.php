<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Connector\ArrayConverter\FlatToStandard;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\NumberAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\AttributeDetails;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindAttributesDetailsInterface;
use Akeneo\ReferenceEntity\Infrastructure\Connector\ArrayConverter\FlatToStandard\Record;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Akeneo\Tool\Component\Connector\Exception\DataArrayConversionException;
use PhpSpec\ObjectBehavior;
use PhpSpec\Wrapper\Subject;

class RecordSpec extends ObjectBehavior
{
    function let(
        FieldsRequirementChecker $fieldsChecker,
        FindAttributesDetailsInterface $findAttributesDetails
    ) {
        $this->beConstructedWith($fieldsChecker, $findAttributesDetails);

        $findAttributesDetails->find(ReferenceEntityIdentifier::fromString('brand'))->willReturn([
            $this->buildAttributeDetails('text', TextAttribute::ATTRIBUTE_TYPE, false, false),
            $this->buildAttributeDetails('number', NumberAttribute::ATTRIBUTE_TYPE, true, false),
            $this->buildAttributeDetails('option', OptionAttribute::ATTRIBUTE_TYPE, false, true),
            $this->buildAttributeDetails('record', RecordAttribute::ATTRIBUTE_TYPE, true, true),
            $this->buildAttributeDetails('records', RecordCollectionAttribute::ATTRIBUTE_TYPE, false, false),
            $this->buildAttributeDetails('options', OptionCollectionAttribute::ATTRIBUTE_TYPE, false, false),
            $this->buildAttributeDetails('picture', ImageAttribute::ATTRIBUTE_TYPE, true, false),
            $this->buildAttributeDetails('localizable_scopable_attribute', TextAttribute::ATTRIBUTE_TYPE, true, true),
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(Record::class);
    }

    function it_is_an_array_converter()
    {
        $this->shouldImplement(ArrayConverterInterface::class);
    }

    function it_converts_an_item_successfully(FieldsRequirementChecker $fieldsChecker)
    {
        $item = $this->anItem();
        $fieldsChecker->checkFieldsPresence($item, ['referenceEntityIdentifier', 'code'])->shouldBeCalled();

        $convertedItem = $this->convert($item, [Record::DIRECTORY_PATH_OPTION_KEY => '/tmp/job']);
        $this->theItemShouldBeConverted($convertedItem);
    }

    function it_throws_an_exception_when_an_attribute_is_not_found(FieldsRequirementChecker $fieldsChecker)
    {
        $item = $this->anItemWithUnknownAttribute();
        $fieldsChecker->checkFieldsPresence($item, ['referenceEntityIdentifier', 'code'])->shouldBeCalled();

        $this->shouldThrow(
            new DataArrayConversionException('Unable to find the "unknown" attribute in the "brand" reference entity')
        )->during('convert', [$item, [Record::DIRECTORY_PATH_OPTION_KEY => '/tmp/job']]);
    }

    function it_throws_an_exception_when_directory_path_is_not_in_options()
    {
        $item = $this->anItem();

        $this->shouldThrow(\InvalidArgumentException::class)->during('convert', [$item, ['other' => 'option']]);
    }

    private function anItem(): array
    {
        return [
            'referenceEntityIdentifier'  => 'brand',
            'code' => 'ref1',
            'label-en_US' => 'My record',
            'label-fr_FR' => 'Mon record',
            'text' => 'data1',
            'number-en_US' => 'data2_en',
            'number-fr_FR' => 'data2_fr',
            'option-ecommerce' => 'data3',
            'option-en_US-ecommerce' => 'conversion should work, the validation will fail later (not localizable)',
            'record-en_US-ecommerce' => 'data41',
            'record-fr_FR-mobile' => 'data42',
            'records' => 'record1,record2',
            'options' => 'option1,option2',
            'picture-en_US' => 'ref1/picture.jpg',
            'picture-fr_FR' => '',
            'unknown' => '',
            '' => '',
            'localizable_scopable_attribute' => '',
            'localizable_scopable_attribute-fr_FR' => '',
       ];
    }

    private function anItemWithUnknownAttribute(): array
    {
        return [
            'referenceEntityIdentifier'  => 'brand',
            'code' => 'ref1',
            'unknown' => 'data',
       ];
    }

    private function theItemShouldBeConverted(Subject $convertedItem)
    {
        $convertedItem->shouldBeArray();
        $convertedItem->shouldHaveCount(3);
        $convertedItem['reference_entity_identifier']->shouldBe('brand');
        $convertedItem['code']->shouldBe('ref1');
        $convertedItem['values']->shouldBeArray();
        $convertedItem['values']->shouldHaveCount(8);
        $convertedItem['values']['label']->shouldBeLike([
            [
                'channel' => null,
                'locale' => 'en_US',
                'data' => 'My record',
            ],
            [
                'channel' => null,
                'locale' => 'fr_FR',
                'data' => 'Mon record',
            ],
        ]);
        $convertedItem['values']['text']->shouldBeLike([[
            'channel' => null,
            'locale' => null,
            'data' => 'data1',
        ]]);
        $convertedItem['values']['number']->shouldBeLike([
            [
                'channel' => null,
                'locale' => 'en_US',
                'data' => 'data2_en',
            ],
            [
                'channel' => null,
                'locale' => 'fr_FR',
                'data' => 'data2_fr',
            ],
        ]);
        $convertedItem['values']['option']->shouldBeLike([
            [
                'channel' => 'ecommerce',
                'locale' => null,
                'data' => 'data3',
            ],
            [
                'channel' => 'ecommerce',
                'locale' => 'en_US',
                'data' => 'conversion should work, the validation will fail later (not localizable)',
            ],
        ]);
        $convertedItem['values']['record']->shouldBeLike([
            [
                'channel' => 'ecommerce',
                'locale' => 'en_US',
                'data' => 'data41',
            ],
            [
                'channel' => 'mobile',
                'locale' => 'fr_FR',
                'data' => 'data42',
            ],
        ]);
        $convertedItem['values']['records']->shouldBeLike([[
            'channel' => null,
            'locale' => null,
            'data' => ['record1', 'record2'],
        ]]);
        $convertedItem['values']['options']->shouldBeLike([[
            'channel' => null,
            'locale' => null,
            'data' => ['option1', 'option2'],
        ]]);
        $convertedItem['values']['picture']->shouldBeLike([
            ['channel' => null, 'locale' => 'en_US', 'data' => '/tmp/job/ref1/picture.jpg'],
            ['channel' => null, 'locale' => 'fr_FR', 'data' => ''],
        ]);
    }

    private function buildAttributeDetails(
        string $code,
        string $type,
        bool $valuePerLocale,
        bool $valuePerChannel
    ): AttributeDetails {
        $attributeDetails = new AttributeDetails();
        $attributeDetails->type = $type;
        $attributeDetails->code = $code;
        $attributeDetails->valuePerLocale = $valuePerLocale;
        $attributeDetails->valuePerChannel = $valuePerChannel;

        return $attributeDetails;
    }
}
