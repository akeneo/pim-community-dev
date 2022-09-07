<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Domain\ValueObject;

use Akeneo\Category\Domain\ValueObject\ValueCollection;
use Akeneo\Category\Infrastructure\Exception\StructureArrayConversionException;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ValueCollectionSpec extends ObjectBehavior
{
    public function it_create_value_on_empty_value_collection_when_set_value(): void
    {
        $this->beConstructedThrough('fromArray', [[]]);
        $this->shouldHaveType(ValueCollection::class);

        $identifier = 'seo_meta_description' . ValueCollection::SEPARATOR . '69e251b3-b876-48b5-9c09-92f54bfb528d';
        $compositeKey = $identifier . ValueCollection::SEPARATOR . 'en_US';

        $expectedData = ValueCollection::fromArray([
            'attribute_codes' => [$identifier],
            $compositeKey => [
                'data' => 'Meta shoes',
                'locale' => 'en_US',
                'attribute_code' => 'seo_meta_description' . ValueCollection::SEPARATOR . '69e251b3-b876-48b5-9c09-92f54bfb528d'
            ]
        ]);

        $this->setValue(
            attributeUuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
            attributeCode: 'seo_meta_description',
            localeCode: 'en_US',
            value: 'Meta shoes'
        )->shouldBeLike($expectedData);
    }

    public function it_add_value_when_set_value(): void
    {
        $initValueCollection = [
            'attribute_codes' => [
                'description' . ValueCollection::SEPARATOR . '840fcd1a-f66b-4f0c-9bbd-596629732950',
            ],
            'description' . ValueCollection::SEPARATOR . '840fcd1a-f66b-4f0c-9bbd-596629732950' . ValueCollection::SEPARATOR . 'en_US' => [
                'data' => 'Description',
                'locale' => 'en_US',
                'attribute_code' => 'description' . ValueCollection::SEPARATOR . '840fcd1a-f66b-4f0c-9bbd-596629732950'
            ],
        ];
        $this->beConstructedThrough('fromArray', [$initValueCollection]);
        $this->shouldHaveType(ValueCollection::class);

        $expectedValues = ValueCollection::fromArray(
            [
                'attribute_codes' => [
                    'description' . ValueCollection::SEPARATOR . '840fcd1a-f66b-4f0c-9bbd-596629732950',
                    'seo_meta_description' . ValueCollection::SEPARATOR . '69e251b3-b876-48b5-9c09-92f54bfb528d',
                ],
                'description' . ValueCollection::SEPARATOR . '840fcd1a-f66b-4f0c-9bbd-596629732950' . ValueCollection::SEPARATOR . 'en_US' => [
                    'data' => 'Description',
                    'locale' => 'en_US',
                    'attribute_code' => 'description' . ValueCollection::SEPARATOR . '840fcd1a-f66b-4f0c-9bbd-596629732950'
                ],
                'seo_meta_description' . ValueCollection::SEPARATOR . '69e251b3-b876-48b5-9c09-92f54bfb528d' . ValueCollection::SEPARATOR . 'en_US' => [
                    'data' => 'Meta shoes',
                    'locale' => 'en_US',
                    'attribute_code' => 'seo_meta_description' . ValueCollection::SEPARATOR . '69e251b3-b876-48b5-9c09-92f54bfb528d'
                ]
            ]
        );

        $this->setValue(
            attributeUuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
            attributeCode: 'seo_meta_description',
            localeCode: 'en_US',
            value: 'Meta shoes'
        )->shouldBeLike($expectedValues);
    }

    public function it_could_not_have_duplicate_attribute_codes_when_set_value(): void
    {
        $duplicateUuid = '840fcd1a-f66b-4f0c-9bbd-596629732950';
        $duplicateCode = 'description';

        $initValueCollection = [
            'attribute_codes' => [
                $duplicateCode . ValueCollection::SEPARATOR . $duplicateUuid,
            ],
            $duplicateCode . ValueCollection::SEPARATOR . $duplicateUuid . ValueCollection::SEPARATOR . 'en_US' => [
                'data' => 'My description',
                'locale' => 'en_US',
                'attribute_code' => $duplicateCode . ValueCollection::SEPARATOR . $duplicateUuid
            ],
        ];
        $this->beConstructedThrough('fromArray', [$initValueCollection]);
        $this->shouldHaveType(ValueCollection::class);

        $expectedValues = ValueCollection::fromArray($initValueCollection);

        $this->setValue(
            attributeUuid: $duplicateUuid,
            attributeCode: $duplicateCode,
            localeCode: 'en_US',
            value: 'My description'
        )->shouldBeLike($expectedValues);
    }

    public function it_update_values_on_duplicate_composite_key_when_set_value(): void
    {
        $uuid = '840fcd1a-f66b-4f0c-9bbd-596629732950';
        $code = 'description';
        $locale = 'en_US';
        $identifier = $code . ValueCollection::SEPARATOR . $uuid;

        $duplicateCompositeKey = $code
            . ValueCollection::SEPARATOR . $uuid
            . ValueCollection::SEPARATOR . $locale;

        $newValue = 'New Description Value';

        $initValueCollection = [
            'attribute_codes' => [$identifier],
            $duplicateCompositeKey => [
                'data' => 'Description',
                'locale' => $locale,
                'attribute_code' => $identifier
            ],
        ];
        $this->beConstructedThrough('fromArray', [$initValueCollection]);
        $this->shouldHaveType(ValueCollection::class);

        $expectedValues = ValueCollection::fromArray(
            [
                'attribute_codes' => [$identifier],
                $duplicateCompositeKey => [
                    'data' => $newValue,
                    'locale' => $locale,
                    'attribute_code' => $identifier
                ],
        ]);

        $this->setValue(
            attributeUuid: $uuid,
            attributeCode: $code,
            localeCode: $locale,
            value: $newValue
        )->shouldBeLike($expectedValues);
    }

    public function it_create_composite_key_with_locale_when_set_value(): void
    {
        $expectedCompositeKey = 'seo_meta_description'
            . ValueCollection::SEPARATOR . '840fcd1a-f66b-4f0c-9bbd-596629732950';

        $expectedLocaleCompositeKey = $expectedCompositeKey
            . ValueCollection::SEPARATOR . 'en_US';

        $expectedValueCollection = ValueCollection::fromArray([
            'attribute_codes' => [$expectedCompositeKey],
            $expectedLocaleCompositeKey => [
                'data' => 'My meta SEO Description Value',
                'locale' => 'en_US',
                'attribute_code' => $expectedCompositeKey
            ],
        ]);
        $this->beConstructedThrough('fromArray', [[]]);
        $this->shouldHaveType(ValueCollection::class);

        $this->setValue(
            attributeUuid: '840fcd1a-f66b-4f0c-9bbd-596629732950',
            attributeCode: 'seo_meta_description',
            localeCode: 'en_US',
            value: 'My meta SEO Description Value'
        )->shouldBeLike($expectedValueCollection);
    }

    public function it_create_composite_key_with_no_locale_when_set_value(): void
    {
        $expectedCompositeKey = 'seo_meta_description'
            . ValueCollection::SEPARATOR . '840fcd1a-f66b-4f0c-9bbd-596629732950';

        $expectedValueCollection = ValueCollection::fromArray([
            'attribute_codes' => [$expectedCompositeKey],
            $expectedCompositeKey => [
                'data' => 'My meta SEO Description Value',
                'locale' => null,
                'attribute_code' => $expectedCompositeKey
            ],
        ]);
        $this->beConstructedThrough('fromArray', [[]]);
        $this->shouldHaveType(ValueCollection::class);

        $this->setValue(
            attributeUuid: '840fcd1a-f66b-4f0c-9bbd-596629732950',
            attributeCode: 'seo_meta_description',
            localeCode: null,
            value: 'My meta SEO Description Value'
        )->shouldBeLike($expectedValueCollection);
    }

    public function it_throw_structure_array_conversion_exception_when_create_value_with_wrong_format(): void
    {
        $this->beConstructedThrough('fromArray', [[
            'attribute_codes' => [],
            'seo_meta_description' . ValueCollection::SEPARATOR . '840fcd1a-f66b-4f0c-9bbd-596629732950' => [
                'data' => 'My meta SEO Description Value',
                'locale' => null,
                'attribute_code' => 'seo_meta_description' . ValueCollection::SEPARATOR . '840fcd1a-f66b-4f0c-9bbd-596629732950'
            ],
        ]]);
        $this->shouldHaveType(ValueCollection::class);
        $this->shouldThrow(StructureArrayConversionException::class)->duringInstantiation();
    }
}
