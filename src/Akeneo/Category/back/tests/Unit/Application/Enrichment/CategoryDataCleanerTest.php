<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Unit\Application\Enrichment;

use Akeneo\Category\Application\Enrichment\CategoryDataCleaner;
use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Application\Storage\UpdateCategoryEnrichedValues;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Model\Attribute\AttributeText;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeAdditionalProperties;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsLocalizable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsRequired;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsScopable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeOrder;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\ImageDataValue;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Category\Domain\ValueObject\ValueCollection;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type ImageData from ImageDataValue
 */
class CategoryDataCleanerTest extends CategoryTestCase
{
    public function testItCallsExecuteWithRightArgumentForChannelOrLocalesCleaning(): void
    {
        $updateCategoryEnrichedValuesMock = $this->createMock(UpdateCategoryEnrichedValues::class);
        $updateCategoryEnrichedValuesMock
            ->expects(self::once())
            ->method('execute')
            ->with($this->getExpectedArgumentForChannelOrLocalesCleaning());
        $getTemplateAttributesByTemplateUuidMock = $this->createMock(GetAttribute::class);
        $categoryDataCleaner = new CategoryDataCleaner(
            $updateCategoryEnrichedValuesMock,
            $getTemplateAttributesByTemplateUuidMock,
        );
        $categoryDataCleaner->cleanByChannelOrLocales(
            [
                'category_1' => ValueCollection::fromDatabase($this->getValuesByCodeForCategory1()),
                'category_2' => ValueCollection::fromDatabase($this->getValuesByCodeForCategory2()),
            ],
            'mobile',
            [],
        );
    }

    public function testItCallsExecuteWithRightArgumentForTemplateUuidCleaning(): void
    {
        $templateUuid = '33abe873-dc17-4da5-aecd-f7a2ae5ef116';
        $updateCategoryEnrichedValuesMock = $this->createMock(UpdateCategoryEnrichedValues::class);
        $updateCategoryEnrichedValuesMock
            ->expects(self::once())
            ->method('execute')
            ->with($this->getExpectedArgumentForTemplateUuidCleaning());
        $getTemplateAttributesByTemplateUuidMock = $this->createMock(
            GetAttribute::class
        );
        $getTemplateAttributesByTemplateUuidMock->method('byTemplateUuid')
            ->with(TemplateUuid::fromString($templateUuid))
            ->willReturn(
                AttributeCollection::fromArray(
                    [
                        AttributeText::create(
                            AttributeUuid::fromString('be2a1d6e-0563-409a-8407-0be494c34b84'),
                            new AttributeCode('seo_keywords'),
                            AttributeOrder::fromInteger(3),
                            AttributeIsRequired::fromBoolean(false),
                            AttributeIsScopable::fromBoolean(true),
                            AttributeIsLocalizable::fromBoolean(true),
                            LabelCollection::fromArray(['en_US' => 'URL slug']),
                            TemplateUuid::fromString('637d8002-44c9-490e-9bb6-258c139da176'),
                            AttributeAdditionalProperties::fromArray([]),
                        ),
                    ]
                )
        );
        $categoryDataCleaner = new CategoryDataCleaner(
            $updateCategoryEnrichedValuesMock,
            $getTemplateAttributesByTemplateUuidMock,
        );

        $categoryDataCleaner->cleanByTemplateUuid(
            ['category_3' => ValueCollection::fromDatabase($this->getValuesByCodeForCategory3()),],
            $templateUuid
        );
    }

    /**
     * @return array<string, array{
     *     data: string|ImageData|null,
     *     type: string,
     *     channel: string|null,
     *     locale: string|null,
     *     attribute_code: string,
     * }>
     *
     * @throws \JsonException
     */
    private function getValuesByCodeForCategory1(): array
    {
        return json_decode('{
            "attribute_codes": [
                "long_description|c91e6a4e-733b-4d77-aefc-129edbf03233"
            ],
            "long_description|c91e6a4e-733b-4d77-aefc-129edbf03233|ecommerce|fr_FR": {
                "data": "<p>Ma description enrichie pour le ecommerce</p>",
                "type": "textarea",
                "locale": "fr_FR",
                "channel": "ecommerce",
                "attribute_code": "long_description|c91e6a4e-733b-4d77-aefc-129edbf03233"
            },
            "long_description|c91e6a4e-733b-4d77-aefc-129edbf03233|mobile|fr_FR": {
                "data": "<p>Ma description enrichie pour le mobile</p>",
                "type": "textarea",
                "locale": "fr_FR",
                "channel": "mobile",
                "attribute_code": "long_description|c91e6a4e-733b-4d77-aefc-129edbf03233"
            },
            "long_description|c91e6a4e-733b-4d77-aefc-129edbf03233|mobile|en_US": {
                "data": "<p>My enriched description for mobile</p>",
                "type": "textarea",
                "locale": "en_US",
                "channel": "mobile",
                "attribute_code": "long_description|c91e6a4e-733b-4d77-aefc-129edbf03233"
            }
        }', true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, array{
     *     data: string|ImageData|null,
     *     type: string,
     *     channel: string|null,
     *     locale: string|null,
     *     attribute_code: string,
     * }>
     *
     * @throws \JsonException
     */
    private function getValuesByCodeForCategory2(): array
    {
        return json_decode('{
            "attribute_codes": [
                "long_description|c91e6a4e-733b-4d77-aefc-129edbf03233"
            ],
            "long_description|c91e6a4e-733b-4d77-aefc-129edbf03233|print|fr_FR": {
                "data": "<p>Ma description enrichie pour l\'imprimerie</p>",
                "type": "textarea",
                "locale": "fr_FR",
                "channel": "ecommerce",
                "attribute_code": "long_description|c91e6a4e-733b-4d77-aefc-129edbf03233"
            }
        }', true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, array{
     *     data: string|ImageData|null,
     *     type: string,
     *     channel: string|null,
     *     locale: string|null,
     *     attribute_code: string,
     * }>
     *
     * @throws \JsonException
     */
    private function getValuesByCodeForCategory3(): array
    {
        return json_decode('{
            "attribute_codes": [
                "url_slug|d8617b1f-1db8-4e49-a6b0-404935fe2911",
                "seo_keywords|be2a1d6e-0563-409a-8407-0be494c34b84"
            ],
            "url_slug|d8617b1f-1db8-4e49-a6b0-404935fe2911": {
                "data": "all_scope_all_locale_url_slug",
                "type": "text",
                "locale": null,
                "channel": null,
                "attribute_code": "url_slug|d8617b1f-1db8-4e49-a6b0-404935fe2911"
            },
            "seo_keywords|be2a1d6e-0563-409a-8407-0be494c34b84": {
                "data": "all_scope_all_locale_seo_keywords",
                "type": "text",
                "locale": null,
                "channel": null,
                "attribute_code": "seo_keywords|be2a1d6e-0563-409a-8407-0be494c34b84"
            }
        }', true, 512, JSON_THROW_ON_ERROR);
    }
    

    /**
     * @return array<string, ValueCollection>
     *
     * @throws \JsonException
     */
    private function getExpectedArgumentForChannelOrLocalesCleaning(): array
    {
        return [
            'category_1' => ValueCollection::fromDatabase([
                'attribute_codes' => [
                    'long_description|c91e6a4e-733b-4d77-aefc-129edbf03233',
                ],
                'long_description|c91e6a4e-733b-4d77-aefc-129edbf03233|ecommerce|fr_FR' => [
                    'data' => '<p>Ma description enrichie pour le ecommerce</p>',
                    'type' => 'textarea',
                    'locale' => 'fr_FR',
                    'channel' => 'ecommerce',
                    'attribute_code' => 'long_description|c91e6a4e-733b-4d77-aefc-129edbf03233',
                ],
            ]),
        ];
    }

    /**
     * @return array<string, ValueCollection>
     *
     * @throws \JsonException
     */
    private function getExpectedArgumentForTemplateUuidCleaning(): array
    {
        return [
            'category_3' => ValueCollection::fromDatabase([
                'attribute_codes' => [
                    'url_slug|d8617b1f-1db8-4e49-a6b0-404935fe2911',
                ],
                'url_slug|d8617b1f-1db8-4e49-a6b0-404935fe2911' => [
                    'data' => 'all_scope_all_locale_url_slug',
                    'type' => 'text',
                    'locale' => null,
                    'channel' => null,
                    'attribute_code' => 'url_slug|d8617b1f-1db8-4e49-a6b0-404935fe2911'
                ],
            ]),
        ];
    }
}
