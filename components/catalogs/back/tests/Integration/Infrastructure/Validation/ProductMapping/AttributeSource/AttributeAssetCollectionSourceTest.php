<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation\ProductMapping\AttributeSource;

use Akeneo\Catalogs\Infrastructure\Validation\ProductMapping\AttributeSource\AttributeAssetCollectionSource;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Validation\ProductMapping\AttributeSource\AttributeAssetCollectionSource
 */
class AttributeAssetCollectionSourceTest extends AbstractAttributeSourceTest
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @dataProvider validDataProvider
     */
    public function testItReturnsNoViolation(array $attribute, array $assetAttribute, array $source): void
    {
        $this->createAttribute($attribute);
        $this->createAssetAttribute($assetAttribute);

        $violations = self::getContainer()->get(ValidatorInterface::class)->validate($source, new AttributeAssetCollectionSource());

        $this->assertEmpty($violations);
    }

    public function validDataProvider(): array
    {
        return [
            'localizable and scopable attribute and asset attribute' => [
                'attribute' => [
                    'code' => 'brands',
                    'type' => 'pim_catalog_asset_collection',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                    'asset_family' => 'atmosphere',
                ],
                'assetAttribute' => [
                    'identifier' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                    'label' => 'Nikke',
                    'type' => 'text',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 'brands',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                    'parameters' => [
                        'sub_source' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                        'sub_scope' => 'ecommerce',
                        'sub_locale' => 'en_US',
                    ],
                ],
            ],
            'scopable attribute and asset attribute' => [
                'attribute' => [
                    'code' => 'brands',
                    'type' => 'pim_catalog_asset_collection',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => false,
                    'asset_family' => 'atmosphere',
                ],
                'assetAttribute' => [
                    'identifier' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                    'label' => 'Nikke',
                    'type' => 'text',
                    'scopable' => true,
                    'localizable' => false,
                ],
                'source' => [
                    'source' => 'brands',
                    'scope' => 'ecommerce',
                    'locale' => null,
                    'parameters' => [
                        'sub_source' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                        'sub_scope' => 'ecommerce',
                        'sub_locale' => null,
                    ],
                ],
            ],
            'localizable attribute and asset attribute' => [
                'attribute' => [
                    'code' => 'brands',
                    'type' => 'pim_catalog_asset_collection',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => true,
                    'asset_family' => 'atmosphere',
                ],
                'assetAttribute' => [
                    'identifier' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                    'label' => 'Nikke',
                    'type' => 'text',
                    'scopable' => false,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 'brands',
                    'scope' => null,
                    'locale' => 'en_US',
                    'parameters' => [
                        'sub_source' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                        'sub_scope' => null,
                        'sub_locale' => 'en_US',
                    ],
                ],
            ],
            'non localizable and non scopable attribute and asset attribute' => [
                'attribute' => [
                    'code' => 'brands',
                    'type' => 'pim_catalog_asset_collection',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                    'asset_family' => 'atmosphere',
                ],
                'assetAttribute' => [
                    'identifier' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                    'label' => 'Nikke',
                    'type' => 'text',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'source' => [
                    'source' => 'brands',
                    'scope' => null,
                    'locale' => null,
                    'parameters' => [
                        'sub_source' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                        'sub_scope' => null,
                        'sub_locale' => null,
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider invalidDataProvider
     */
    public function testItReturnsViolationsWhenInvalid(
        array $attribute,
        array $assetAttribute,
        array $source,
        string $expectedMessage,
    ): void {
        $this->createAttribute($attribute);
        $this->createAssetAttribute($assetAttribute);

        $violations = self::getContainer()->get(ValidatorInterface::class)->validate($source, new AttributeAssetCollectionSource());

        $this->assertViolationsListContains($violations, $expectedMessage);
    }

    public function invalidDataProvider(): array
    {
        return [
            'invalid source value' => [
                'attribute' => [
                    'code' => 'brands',
                    'type' => 'pim_catalog_asset_collection',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                    'asset_family' => 'atmosphere',
                ],
                'assetAttribute' => [
                    'identifier' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                    'label' => 'Nikke',
                    'type' => 'text',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'source' => [
                    'source' => 42,
                    'scope' => null,
                    'locale' => null,
                    'parameters' => [
                        'sub_source' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                        'sub_scope' => null,
                        'sub_locale' => null,
                    ],
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'missing scope' => [
                'attribute' => [
                    'code' => 'brands',
                    'type' => 'pim_catalog_asset_collection',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => false,
                    'asset_family' => 'atmosphere',
                ],
                'assetAttribute' => [
                    'identifier' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                    'label' => 'Nikke',
                    'type' => 'text',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'source' => [
                    'source' => 'brands',
                    'scope' => null,
                    'locale' => null,
                    'parameters' => [
                        'sub_source' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                        'sub_scope' => null,
                        'sub_locale' => null,
                    ],
                ],
                'expectedMessage' => 'This channel must not be empty.',
            ],
            'invalid scope' => [
                'attribute' => [
                    'code' => 'brands',
                    'type' => 'pim_catalog_asset_collection',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                    'asset_family' => 'atmosphere',
                ],
                'assetAttribute' => [
                    'identifier' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                    'label' => 'Nikke',
                    'type' => 'text',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'source' => [
                    'source' => 'brands',
                    'scope' => 42,
                    'locale' => 'en_US',
                    'parameters' => [
                        'sub_source' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                        'sub_scope' => null,
                        'sub_locale' => null,
                    ],
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'unknown scope' => [
                'attribute' => [
                    'code' => 'brands',
                    'type' => 'pim_catalog_asset_collection',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => false,
                    'asset_family' => 'atmosphere',
                ],
                'assetAttribute' => [
                    'identifier' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                    'label' => 'Nikke',
                    'type' => 'text',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'source' => [
                    'source' => 'brands',
                    'scope' => 'unknown_scope',
                    'locale' => null,
                    'parameters' => [
                        'sub_source' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                        'sub_scope' => null,
                        'sub_locale' => null,
                    ],
                ],
                'expectedMessage' => 'This channel has been deleted. Please check your channel settings or update this value.',
            ],
            'missing locale' => [
                'attribute' => [
                    'code' => 'brands',
                    'type' => 'pim_catalog_asset_collection',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => true,
                    'asset_family' => 'atmosphere',
                ],
                'assetAttribute' => [
                    'identifier' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                    'label' => 'Nikke',
                    'type' => 'text',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'source' => [
                    'source' => 'brands',
                    'scope' => null,
                    'locale' => null,
                    'parameters' => [
                        'sub_source' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                        'sub_scope' => null,
                        'sub_locale' => null,
                    ],
                ],
                'expectedMessage' => 'This locale must not be empty.',
            ],
            'invalid locale' => [
                'attribute' => [
                    'code' => 'brands',
                    'type' => 'pim_catalog_asset_collection',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                    'asset_family' => 'atmosphere',
                ],
                'assetAttribute' => [
                    'identifier' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                    'label' => 'Nikke',
                    'type' => 'text',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'source' => [
                    'source' => 'brands',
                    'scope' => 'ecommerce',
                    'locale' => 42,
                    'parameters' => [
                        'sub_source' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                        'sub_scope' => null,
                        'sub_locale' => null,
                    ],
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'unknown locale' => [
                'attribute' => [
                    'code' => 'brands',
                    'type' => 'pim_catalog_asset_collection',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => true,
                    'asset_family' => 'atmosphere',
                ],
                'assetAttribute' => [
                    'identifier' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                    'label' => 'Nikke',
                    'type' => 'text',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'source' => [
                    'source' => 'brands',
                    'scope' => null,
                    'locale' => 'kz_KZ',
                    'parameters' => [
                        'sub_source' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                        'sub_scope' => null,
                        'sub_locale' => null,
                    ],
                ],
                'expectedMessage' => 'This locale is disabled or does not exist anymore. Please check your channels and locales settings.',
            ],
            'disabled locale for a channel' => [
                'attribute' => [
                    'code' => 'brands',
                    'type' => 'pim_catalog_asset_collection',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                    'asset_family' => 'atmosphere',
                ],
                'assetAttribute' => [
                    'identifier' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                    'label' => 'Nikke',
                    'type' => 'text',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'source' => [
                    'source' => 'brands',
                    'scope' => 'ecommerce',
                    'locale' => 'kz_KZ',
                    'parameters' => [
                        'sub_source' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                        'sub_scope' => null,
                        'sub_locale' => null,
                    ],
                ],
                'expectedMessage' => 'This locale is disabled. Please check your channels and locales settings or update this value.',
            ],
            'missing parameters' => [
                'attribute' => [
                    'code' => 'brands',
                    'type' => 'pim_catalog_asset_collection',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                    'asset_family' => 'atmosphere',
                ],
                'assetAttribute' => [
                    'identifier' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                    'label' => 'Nikke',
                    'type' => 'text',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'source' => [
                    'source' => 'brands',
                    'scope' => null,
                    'locale' => null,
                ],
                'expectedMessage' => 'This field is missing.',
            ],
            'missing sub_source' => [
                'attribute' => [
                    'code' => 'brands',
                    'type' => 'pim_catalog_asset_collection',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                    'asset_family' => 'atmosphere',
                ],
                'assetAttribute' => [
                    'identifier' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                    'label' => 'Nikke',
                    'type' => 'text',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'source' => [
                    'source' => 'brands',
                    'scope' => null,
                    'locale' => null,
                    'parameters' => [
                        'sub_scope' => null,
                        'sub_locale' => null,
                    ],
                ],
                'expectedMessage' => 'This field is missing.',
            ],
            'invalid sub_source' => [
                'attribute' => [
                    'code' => 'brands',
                    'type' => 'pim_catalog_asset_collection',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                    'asset_family' => 'atmosphere',
                ],
                'assetAttribute' => [
                    'identifier' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                    'label' => 'Nikke',
                    'type' => 'text',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'source' => [
                    'source' => 'brands',
                    'scope' => null,
                    'locale' => null,
                    'parameters' => [
                        'sub_source' => 42,
                        'sub_scope' => null,
                        'sub_locale' => null,
                    ],
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'unknown sub_source' => [
                'attribute' => [
                    'code' => 'brands',
                    'type' => 'pim_catalog_asset_collection',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                    'asset_family' => 'atmosphere',
                ],
                'assetAttribute' => [
                    'identifier' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                    'label' => 'Nikke',
                    'type' => 'text',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'source' => [
                    'source' => 'brands',
                    'scope' => null,
                    'locale' => null,
                    'parameters' => [
                        'sub_source' => 'unknown',
                        'sub_scope' => null,
                        'sub_locale' => null,
                    ],
                ],
                'expectedMessage' => 'This sub attribute has been deleted.',
            ],
            'missing sub_scope' => [
                'attribute' => [
                    'code' => 'brands',
                    'type' => 'pim_catalog_asset_collection',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                    'asset_family' => 'atmosphere',
                ],
                'assetAttribute' => [
                    'identifier' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                    'label' => 'Nikke',
                    'type' => 'text',
                    'scopable' => true,
                    'localizable' => false,
                ],
                'source' => [
                    'source' => 'brands',
                    'scope' => null,
                    'locale' => null,
                    'parameters' => [
                        'sub_source' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                        'sub_scope' => null,
                        'sub_locale' => null,
                    ],
                ],
                'expectedMessage' => 'This channel must not be empty.',
            ],
            'invalid sub_scope' => [
                'attribute' => [
                    'code' => 'brands',
                    'type' => 'pim_catalog_asset_collection',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                    'asset_family' => 'atmosphere',
                ],
                'assetAttribute' => [
                    'identifier' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                    'label' => 'Nikke',
                    'type' => 'text',
                    'scopable' => true,
                    'localizable' => false,
                ],
                'source' => [
                    'source' => 'brands',
                    'scope' => null,
                    'locale' => null,
                    'parameters' => [
                        'sub_source' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                        'sub_scope' => 42,
                        'sub_locale' => null,
                    ],
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'unknown sub_scope' => [
                'attribute' => [
                    'code' => 'brands',
                    'type' => 'pim_catalog_asset_collection',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                    'asset_family' => 'atmosphere',
                ],
                'assetAttribute' => [
                    'identifier' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                    'label' => 'Nikke',
                    'type' => 'text',
                    'scopable' => true,
                    'localizable' => false,
                ],
                'source' => [
                    'source' => 'brands',
                    'scope' => null,
                    'locale' => null,
                    'parameters' => [
                        'sub_source' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                        'sub_scope' => 'unknown',
                        'sub_locale' => null,
                    ],
                ],
                'expectedMessage' => 'This channel has been deleted. Please check your channel settings or update this value.',
            ],
            'missing sub_locale' => [
                'attribute' => [
                    'code' => 'brands',
                    'type' => 'pim_catalog_asset_collection',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                    'asset_family' => 'atmosphere',
                ],
                'assetAttribute' => [
                    'identifier' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                    'label' => 'Nikke',
                    'type' => 'text',
                    'scopable' => false,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 'brands',
                    'scope' => null,
                    'locale' => null,
                    'parameters' => [
                        'sub_source' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                        'sub_scope' => null,
                        'sub_locale' => null,
                    ],
                ],
                'expectedMessage' => 'This locale must not be empty.',
            ],
            'invalid sub_locale' => [
                'attribute' => [
                    'code' => 'brands',
                    'type' => 'pim_catalog_asset_collection',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                    'asset_family' => 'atmosphere',
                ],
                'assetAttribute' => [
                    'identifier' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                    'label' => 'Nikke',
                    'type' => 'text',
                    'scopable' => false,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 'brands',
                    'scope' => null,
                    'locale' => null,
                    'parameters' => [
                        'sub_source' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                        'sub_scope' => null,
                        'sub_locale' => 42,
                    ],
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'unknown sub_locale' => [
                'attribute' => [
                    'code' => 'brands',
                    'type' => 'pim_catalog_asset_collection',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                    'asset_family' => 'atmosphere',
                ],
                'assetAttribute' => [
                    'identifier' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                    'label' => 'Nikke',
                    'type' => 'text',
                    'scopable' => false,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 'brands',
                    'scope' => null,
                    'locale' => null,
                    'parameters' => [
                        'sub_source' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                        'sub_scope' => null,
                        'sub_locale' => 'kz_KZ',
                    ],
                ],
                'expectedMessage' => 'This locale is disabled or does not exist anymore. Please check your channels and locales settings.',
            ],
            'extra field' => [
                'attribute' => [
                    'code' => 'brands',
                    'type' => 'pim_catalog_asset_collection',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                    'asset_family' => 'atmosphere',
                ],
                'assetAttribute' => [
                    'identifier' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                    'label' => 'Nikke',
                    'type' => 'text',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'source' => [
                    'source' => 'brands',
                    'scope' => null,
                    'locale' => null,
                    'category' => 'dimension',
                    'parameters' => [
                        'sub_source' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                        'sub_scope' => null,
                        'sub_locale' => null,
                    ],
                ],
                'expectedMessage' => 'This field was not expected.',
            ],
            'extra parameter field' => [
                'attribute' => [
                    'code' => 'brands',
                    'type' => 'pim_catalog_asset_collection',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                    'asset_family' => 'atmosphere',
                ],
                'assetAttribute' => [
                    'identifier' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                    'label' => 'Nikke',
                    'type' => 'text',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'source' => [
                    'source' => 'brands',
                    'scope' => null,
                    'locale' => null,
                    'parameters' => [
                        'sub_source' => 'label_brand_dc84ebc2-74c6-41c0-b35b-7e4ec27156ad',
                        'sub_scope' => null,
                        'sub_locale' => null,
                        'category' => 'dimension',
                    ],
                ],
                'expectedMessage' => 'This field was not expected.',
            ],
        ];
    }
}
