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
    public function it_gets_value(): void
    {
        $compositeKey = 'seo_meta_description' . ValueCollection::SEPARATOR . '69e251b3-b876-48b5-9c09-92f54bfb528d';
        $localeCompositeKey =
            $compositeKey
            . ValueCollection::SEPARATOR . 'ecommerce'
            . ValueCollection::SEPARATOR . 'en_US';

        $this->beConstructedThrough('fromArray', [[
            'attribute_codes' => [$compositeKey],
            $localeCompositeKey => [
                'data' => 'Meta shoes',
                'channel' => 'ecommerce',
                'locale' => 'en_US',
                'attribute_code' => $compositeKey
            ]
        ]]);
        $this->shouldHaveType(ValueCollection::class);

        $expectedValue = [
            'data' => 'Meta shoes',
            'channel' => 'ecommerce',
            'locale' => 'en_US',
            'attribute_code' => $compositeKey
        ];

        $this->getValue(
            attributeCode: 'seo_meta_description',
            attributeUuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
            localeCode: 'en_US',
            channel: 'ecommerce'
        )->shouldBeLike($expectedValue);
    }

    public function it_returns_null_when_value_not_found(): void
    {
        $compositeKey = 'seo_meta_description' . ValueCollection::SEPARATOR . '69e251b3-b876-48b5-9c09-92f54bfb528d';
        $localeCompositeKey =
            $compositeKey
            . ValueCollection::SEPARATOR . 'ecommerce'
            . ValueCollection::SEPARATOR . 'en_US';

        $this->beConstructedThrough('fromArray', [[
            'attribute_codes' => [$compositeKey],
            $localeCompositeKey => [
                'data' => 'Meta shoes',
                'channel' => 'ecommerce',
                'locale' => 'en_US',
                'attribute_code' => $compositeKey
            ]
        ]]);
        $this->shouldHaveType(ValueCollection::class);

        $this->getValue(
            attributeCode: 'seo_keyword',
            attributeUuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
            localeCode: 'fr_FR',
            channel: 'ecommerce'
        )->shouldBeLike(null);
    }

    public function it_creates_value_on_empty_value_collection_when_set_value(): void
    {
        $this->beConstructedThrough('fromArray', [[]]);
        $this->shouldHaveType(ValueCollection::class);

        $compositeKey = 'seo_meta_description' . ValueCollection::SEPARATOR . '69e251b3-b876-48b5-9c09-92f54bfb528d';
        $localeCompositeKey =
            $compositeKey
            . ValueCollection::SEPARATOR . 'ecommerce'
            . ValueCollection::SEPARATOR . 'en_US';

        $expectedData = ValueCollection::fromArray([
            'attribute_codes' => [$compositeKey],
            $localeCompositeKey => [
                'data' => 'Meta shoes',
                'channel' => 'ecommerce',
                'locale' => 'en_US',
                'attribute_code' => 'seo_meta_description' . ValueCollection::SEPARATOR . '69e251b3-b876-48b5-9c09-92f54bfb528d'
            ]
        ]);

        $this->setValue(
            attributeUuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
            attributeCode: 'seo_meta_description',
            localeCode: 'en_US',
            channel: 'ecommerce',
            value: 'Meta shoes'
        )->shouldBeLike($expectedData);
    }

    public function it_adds_value_when_set_value(): void
    {
        $initValueCollection = [
            'attribute_codes' => [
                'description' . ValueCollection::SEPARATOR . '840fcd1a-f66b-4f0c-9bbd-596629732950',
            ],
            'description' . ValueCollection::SEPARATOR . '840fcd1a-f66b-4f0c-9bbd-596629732950' . ValueCollection::SEPARATOR . 'ecommerce' . ValueCollection::SEPARATOR . 'en_US' => [
                'data' => 'Description',
                'channel' => 'ecommerce',
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
                'description' . ValueCollection::SEPARATOR . '840fcd1a-f66b-4f0c-9bbd-596629732950' . ValueCollection::SEPARATOR . 'ecommerce' . ValueCollection::SEPARATOR . 'en_US' => [
                    'data' => 'Description',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                    'attribute_code' => 'description' . ValueCollection::SEPARATOR . '840fcd1a-f66b-4f0c-9bbd-596629732950'
                ],
                'seo_meta_description' . ValueCollection::SEPARATOR . '69e251b3-b876-48b5-9c09-92f54bfb528d' . ValueCollection::SEPARATOR . 'ecommerce' . ValueCollection::SEPARATOR . 'en_US' => [
                    'data' => 'Meta shoes',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                    'attribute_code' => 'seo_meta_description' . ValueCollection::SEPARATOR . '69e251b3-b876-48b5-9c09-92f54bfb528d'
                ]
            ]
        );

        $this->setValue(
            attributeUuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
            attributeCode: 'seo_meta_description',
            localeCode: 'en_US',
            channel: 'ecommerce',
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
            $duplicateCode . ValueCollection::SEPARATOR . $duplicateUuid . ValueCollection::SEPARATOR . 'ecommerce' . ValueCollection::SEPARATOR . 'en_US' => [
                'data' => 'My description',
                'channel' => 'ecommerce',
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
            channel: 'ecommerce',
            value: 'My description'
        )->shouldBeLike($expectedValues);
    }

    public function it_updates_values_on_duplicate_locale_composite_key_when_set_value(): void
    {
        $uuid = '840fcd1a-f66b-4f0c-9bbd-596629732950';
        $code = 'description';
        $locale = 'en_US';
        $channel = 'ecommerce';
        $compositeKey = $code . ValueCollection::SEPARATOR . $uuid;

        $duplicateLocaleCompositeKey = $code
            . ValueCollection::SEPARATOR . $uuid
            . ValueCollection::SEPARATOR . $channel
            . ValueCollection::SEPARATOR . $locale;

        $newValue = 'New Description Value';

        $initValueCollection = [
            'attribute_codes' => [$compositeKey],
            $duplicateLocaleCompositeKey => [
                'data' => 'Description',
                'channel' => $channel,
                'locale' => $locale,
                'attribute_code' => $compositeKey
            ],
        ];
        $this->beConstructedThrough('fromArray', [$initValueCollection]);
        $this->shouldHaveType(ValueCollection::class);

        $expectedValues = ValueCollection::fromArray(
            [
                'attribute_codes' => [$compositeKey],
                $duplicateLocaleCompositeKey => [
                    'data' => $newValue,
                    'channel' => $channel,
                    'locale' => $locale,
                    'attribute_code' => $compositeKey
                ],
        ]);

        $this->setValue(
            attributeUuid: $uuid,
            attributeCode: $code,
            localeCode: $locale,
            channel: $channel,
            value: $newValue
        )->shouldBeLike($expectedValues);
    }

    public function it_creates_composite_key_with_locale_when_set_value(): void
    {
        $expectedCompositeKey = 'seo_meta_description'
            . ValueCollection::SEPARATOR . '840fcd1a-f66b-4f0c-9bbd-596629732950';

        $expectedLocaleCompositeKey = $expectedCompositeKey
            . ValueCollection::SEPARATOR . 'ecommerce'
            . ValueCollection::SEPARATOR . 'en_US';

        $expectedValueCollection = ValueCollection::fromArray([
            'attribute_codes' => [$expectedCompositeKey],
            $expectedLocaleCompositeKey => [
                'data' => 'My meta SEO Description Value',
                'channel' => 'ecommerce',
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
            channel: 'ecommerce',
            value: 'My meta SEO Description Value'
        )->shouldBeLike($expectedValueCollection);
    }

    public function it_creates_composite_key_with_no_locale_when_setting_value(): void
    {
        $expectedCompositeKey = 'seo_meta_description'
            . ValueCollection::SEPARATOR . '840fcd1a-f66b-4f0c-9bbd-596629732950';

        $expectedLocaleCompositeKey = 'seo_meta_description'
            . ValueCollection::SEPARATOR . '840fcd1a-f66b-4f0c-9bbd-596629732950'
            . ValueCollection::SEPARATOR . 'ecommerce';

        $expectedValueCollection = ValueCollection::fromArray([
            'attribute_codes' => [$expectedCompositeKey],
            $expectedLocaleCompositeKey => [
                'data' => 'My meta SEO Description Value',
                'channel' => 'ecommerce',
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
            channel: 'ecommerce',
            value: 'My meta SEO Description Value'
        )->shouldBeLike($expectedValueCollection);
    }

    public function it_creates_composite_key_with_no_channel_when_setting_value(): void
    {
        $expectedCompositeKey = 'seo_meta_description'
            . ValueCollection::SEPARATOR . '840fcd1a-f66b-4f0c-9bbd-596629732950';

        $expectedLocaleCompositeKey = 'seo_meta_description'
            . ValueCollection::SEPARATOR . '840fcd1a-f66b-4f0c-9bbd-596629732950'
            . ValueCollection::SEPARATOR . 'en_US';

        $expectedValueCollection = ValueCollection::fromArray([
            'attribute_codes' => [$expectedCompositeKey],
            $expectedLocaleCompositeKey => [
                'data' => 'My meta SEO Description Value',
                'channel' => null,
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
            channel: null,
            value: 'My meta SEO Description Value'
        )->shouldBeLike($expectedValueCollection);
    }

    public function it_normalizes_without_attribute_codes_key_value(): void
    {
        $compositeKey = 'seo_meta_description' . ValueCollection::SEPARATOR . '840fcd1a-f66b-4f0c-9bbd-596629732950';
        $this->beConstructedThrough('fromArray', [[
            'attribute_codes' => [$compositeKey],
            $compositeKey => [
                'data' => 'My meta SEO Description Value',
                'channel' => null,
                'locale' => null,
                'attribute_code' => $compositeKey
            ],
        ]]);

        $normalizedValueCollection = [
            $compositeKey => [
                'data' => 'My meta SEO Description Value',
                'channel' => null,
                'locale' => null,
                'attribute_code' => $compositeKey
            ]
        ];

        $this->normalize()->shouldBeLike($normalizedValueCollection);
    }

    public function it_gets_all_values(): void
    {
        $compositeKey = 'seo_meta_description' . ValueCollection::SEPARATOR . '840fcd1a-f66b-4f0c-9bbd-596629732950';
        $this->beConstructedThrough('fromArray', [[
            'attribute_codes' => [$compositeKey],
            $compositeKey => [
                'data' => 'My meta SEO Description Value',
                'channel' => null,
                'locale' => null,
                'attribute_code' => $compositeKey
            ],
        ]]);

        $getValues = [
            'attribute_codes' => [$compositeKey],
            $compositeKey => [
                'data' => 'My meta SEO Description Value',
                'channel' => null,
                'locale' => null,
                'attribute_code' => $compositeKey
            ]
        ];

        $this->getValues()->shouldBeLike($getValues);
    }

    public function it_throws_structure_array_conversion_exception_when_create_value_with_wrong_format(): void
    {
        $this->beConstructedThrough('fromArray', [[
            'attribute_codes' => [],
            'seo_meta_description' . ValueCollection::SEPARATOR . '840fcd1a-f66b-4f0c-9bbd-596629732950' => [
                'data' => 'My meta SEO Description Value',
                'channel' => null,
                'locale' => null,
                'attribute_code' => 'seo_meta_description' . ValueCollection::SEPARATOR . '840fcd1a-f66b-4f0c-9bbd-596629732950'
            ],
        ]]);
        $this->shouldHaveType(ValueCollection::class);
        $this->shouldThrow(StructureArrayConversionException::class)->duringInstantiation();
    }
}
