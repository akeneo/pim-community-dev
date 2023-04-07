<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Domain\ValueObject;

use Akeneo\Category\Domain\Model\Attribute\AttributeText;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\TextValue;
use Akeneo\Category\Domain\ValueObject\ValueCollection;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ValueCollectionSpec extends ObjectBehavior
{
    public function it_gets_value(): void
    {
        $givenValues = [
            TextValue::fromApplier(
                value: 'Meta shoes',
                uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
                code: 'seo_meta_description',
                channel: 'ecommerce',
                locale: 'en_US'
            )
        ];

        $this->beConstructedThrough('fromArray', [$givenValues]);
        $this->shouldHaveType(ValueCollection::class);

        $expectedValue = TextValue::fromApplier(
            value: 'Meta shoes',
            uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
            code: 'seo_meta_description',
            channel: 'ecommerce',
            locale: 'en_US'
        );

        $this->getValue(
            attributeCode: 'seo_meta_description',
            attributeUuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
            channel: 'ecommerce',
            localeCode: 'en_US',
        )->shouldBeLike($expectedValue);
    }

    public function it_returns_null_when_value_not_found(): void
    {
        $givenValues = [
            TextValue::fromApplier(
                value: 'Meta shoes',
                uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
                code: 'seo_meta_description',
                channel: 'ecommerce',
                locale: 'en_US'
            )
        ];

        $this->beConstructedThrough('fromArray', [$givenValues]);
        $this->shouldHaveType(ValueCollection::class);

        $this->getValue(
            attributeCode: 'seo_keyword',
            attributeUuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
            channel: 'ecommerce',
            localeCode: 'fr_FR',
        )->shouldBeLike(null);
    }

    public function it_creates_value_on_empty_value_collection_when_setting_value(): void
    {
        $this->beConstructedThrough('fromArray', [[]]);
        $this->shouldHaveType(ValueCollection::class);

        $expectedData = ValueCollection::fromArray([
            TextValue::fromApplier(
                value: 'Meta shoes',
                uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
                code: 'seo_meta_description',
                channel: 'ecommerce',
                locale: 'en_US'
            )
        ]);

        $setValue = TextValue::fromApplier(
            value: 'Meta shoes',
            uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
            code: 'seo_meta_description',
            channel: 'ecommerce',
            locale: 'en_US'
        );

        $this->setValue($setValue)->shouldBeLike($expectedData);
    }

    public function it_adds_value_when_setting_value(): void
    {
        $givenValues = [
            TextValue::fromApplier(
                value: 'Description',
                uuid: '840fcd1a-f66b-4f0c-9bbd-596629732950',
                code: 'description',
                channel: 'ecommerce',
                locale: 'en_US'
            )
        ];

        $this->beConstructedThrough('fromArray', [$givenValues]);
        $this->shouldHaveType(ValueCollection::class);

        $expectedValues = ValueCollection::fromArray(
            [
                TextValue::fromApplier(
                    value: 'Description',
                    uuid: '840fcd1a-f66b-4f0c-9bbd-596629732950',
                    code: 'description',
                    channel: 'ecommerce',
                    locale: 'en_US'
                ),
                TextValue::fromApplier(
                    value: 'Meta shoes',
                    uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
                    code: 'seo_meta_description',
                    channel: 'ecommerce',
                    locale: 'en_US'
                )
            ]
        );

        $this->setValue(
            TextValue::fromApplier(
                value: 'Meta shoes',
                uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
                code: 'seo_meta_description',
                channel: 'ecommerce',
                locale: 'en_US'
            )
        )->shouldBeLike($expectedValues);
    }

    public function it_could_not_have_duplicate_attribute_codes_when_setting_value(): void
    {
        $givenValue = TextValue::fromApplier(
            value: 'My description',
            uuid: '840fcd1a-f66b-4f0c-9bbd-596629732950',
            code: 'description',
            channel: 'ecommerce',
            locale: 'en_US'
        );

        $this->beConstructedThrough('fromArray', [[$givenValue]]);
        $this->shouldHaveType(ValueCollection::class);

        $this->setValue($givenValue)->shouldHaveCount(1);
    }

    public function it_updates_values_on_duplicate_key_when_setting_value(): void
    {
        $newValue = 'New Description Value';

        $givenValue = TextValue::fromApplier(
            value: 'Description',
            uuid: '840fcd1a-f66b-4f0c-9bbd-596629732950',
            code: 'description',
            channel: 'ecommerce',
            locale: 'en_US'
        );
        $this->beConstructedThrough('fromArray', [[$givenValue]]);
        $this->shouldHaveType(ValueCollection::class);

        $expectedValues = ValueCollection::fromArray([
            TextValue::fromApplier(
                value: $newValue,
                uuid: '840fcd1a-f66b-4f0c-9bbd-596629732950',
                code: 'description',
                channel: 'ecommerce',
                locale: 'en_US'
            )
        ]);

        $this->setValue(
            TextValue::fromApplier(
                value: $newValue,
                uuid: '840fcd1a-f66b-4f0c-9bbd-596629732950',
                code: 'description',
                channel: 'ecommerce',
                locale: 'en_US'
            )
        )->shouldBeLike($expectedValues);
    }

    public function it_normalizes(): void
    {
        $givenDescriptionValue = TextValue::fromApplier(
            value: 'Nice shoes',
            uuid: '840fcd1a-f66b-4f0c-9bbd-596629732950',
            code: 'description',
            channel: 'ecommerce',
            locale: 'en_US'
        );

        $givenSeoDescriptionValue = TextValue::fromApplier(
            value: 'Meta shoes',
            uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
            code: 'seo_meta_description',
            channel: 'ecommerce',
            locale: 'en_US'
        );
        $this->beConstructedThrough('fromArray', [[$givenDescriptionValue, $givenSeoDescriptionValue]]);

        $normalizedValueCollection = [
            'description|840fcd1a-f66b-4f0c-9bbd-596629732950|ecommerce|en_US' => [
                'data' => 'Nice shoes',
                'type' => 'text',
                'channel' => 'ecommerce',
                'locale' => 'en_US',
                'attribute_code' => 'description|840fcd1a-f66b-4f0c-9bbd-596629732950'
            ],
            'seo_meta_description|69e251b3-b876-48b5-9c09-92f54bfb528d|ecommerce|en_US' => [
                'data' => 'Meta shoes',
                'type' => 'text',
                'channel' => 'ecommerce',
                'locale' => 'en_US',
                'attribute_code' => 'seo_meta_description|69e251b3-b876-48b5-9c09-92f54bfb528d'
            ],

        ];

        $this->normalize()->shouldBeLike($normalizedValueCollection);
    }

    public function it_gets_all_values(): void
    {
        $givenDescriptionValue = TextValue::fromApplier(
            value: 'Nice shoes',
            uuid: '840fcd1a-f66b-4f0c-9bbd-596629732950',
            code: 'description',
            channel: 'ecommerce',
            locale: 'en_US'
        );

        $givenSeoDescriptionValue = TextValue::fromApplier(
            value: 'Meta shoes',
            uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
            code: 'seo_meta_description',
            channel: 'ecommerce',
            locale: 'en_US'
        );

        $this->beConstructedThrough('fromArray', [[$givenDescriptionValue, $givenSeoDescriptionValue]]);

        $expectedValues = [
            $givenDescriptionValue,
            $givenSeoDescriptionValue
        ];

        $this->getValues()->shouldBeLike($expectedValues);
    }

    public function it_throws_invalid_argument_exception_when_creating_value_with_wrong_format(AttributeText $givenValue): void
    {
        $this->beConstructedThrough('fromArray', [[$givenValue]]);
        $this->shouldHaveType(ValueCollection::class);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_throws_invalid_argument_exception_when_creating_value_with_duplicate_value(): void
    {
        $givenDuplicateValues = [
            TextValue::fromApplier(
                value: 'description',
                uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
                code: 'seo_meta_description',
                channel: 'ecommerce',
                locale: 'en_US'
            ),
            TextValue::fromApplier(
                value: 'other description',
                uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
                code: 'seo_meta_description',
                channel: 'ecommerce',
                locale: 'en_US'
            ),
        ];
        $this->beConstructedThrough('fromArray', [$givenDuplicateValues]);
        $this->shouldHaveType(ValueCollection::class);
        $this->shouldThrow(new \InvalidArgumentException(
            "Duplicate value for seo_meta_description|69e251b3-b876-48b5-9c09-92f54bfb528d|ecommerce|en_US"
        ))->duringInstantiation();
    }

    public function it_creates_value_collection_from_database()
    {
        $givenDatabaseValues = [
            'seo_meta_description|69e251b3-b876-48b5-9c09-92f54bfb528d|ecommerce|en_us' => [
                'data' => 'Meta shoes',
                'type' => 'text',
                'channel' => 'ecommerce',
                'locale' => 'en_US',
                'attribute_code' => 'seo_meta_description|69e251b3-b876-48b5-9c09-92f54bfb528d'
            ]
        ];

        $expectedValue = TextValue::fromApplier(
            value: 'Meta shoes',
            uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
            code: 'seo_meta_description',
            channel: 'ecommerce',
            locale: 'en_US'
        );

        $this->beConstructedThrough('fromDatabase', [$givenDatabaseValues]);
        $this->shouldHaveType(ValueCollection::class);

        $this->getValues()->shouldHaveCount(1);

        $this->getValue(
            'seo_meta_description',
            '69e251b3-b876-48b5-9c09-92f54bfb528d',
            'ecommerce',
            'en_US',
        )->shouldBeLike($expectedValue);

        $this->getValue(
            'seo_meta_description',
            '69e251b3-b876-48b5-9c09-92f54bfb528d',
            'ecommerce',
            'en_US',
        )->shouldBeAnInstanceOf(TextValue::class);
    }
}
