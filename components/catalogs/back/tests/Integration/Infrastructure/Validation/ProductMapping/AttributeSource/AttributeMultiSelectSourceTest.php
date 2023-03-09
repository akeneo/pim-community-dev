<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation\ProductMapping\AttributeSource;

use Akeneo\Catalogs\Infrastructure\Validation\ProductMapping\AttributeSource\AttributeMultiSelectSource;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Validation\ProductMapping\AttributeSource\AttributeMultiSelectSource
 */
class AttributeMultiSelectSourceTest extends AbstractAttributeSourceTest
{
    private ?ValidatorInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = self::getContainer()->get(ValidatorInterface::class);
    }

    /**
     * @dataProvider validDataProvider
     */
    public function testItReturnsNoViolation(array $attribute, array $source): void
    {
        $this->createAttribute($attribute);

        $violations = $this->validator->validate($source, new AttributeMultiSelectSource());

        $this->assertEmpty($violations);
    }

    public function validDataProvider(): array
    {
        return [
            'localizable and scopable attribute' => [
                'attribute' => [
                    'code' => 'video_output',
                    'type' => 'pim_catalog_multiselect',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                    'options' => ['VGA', 'HDMI', 'DisplayPort', 'miniHDMI', 'miniDisplayPort'],
                ],
                'source' => [
                    'source' => 'video_output',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                    'parameters' => [
                        'label_locale' => 'en_US',
                        'default' => null,
                    ],
                ],
            ],
            'scopable attribute' => [
                'attribute' => [
                    'code' => 'video_output',
                    'type' => 'pim_catalog_multiselect',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => false,
                    'options' => ['VGA', 'HDMI', 'DisplayPort', 'miniHDMI', 'miniDisplayPort'],
                ],
                'source' => [
                    'source' => 'video_output',
                    'scope' => 'ecommerce',
                    'locale' => null,
                    'parameters' => [
                        'label_locale' => 'en_US',
                        'default' => null,
                    ],
                ],
            ],
            'localizable attribute' => [
                'attribute' => [
                    'code' => 'video_output',
                    'type' => 'pim_catalog_multiselect',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => true,
                    'options' => ['VGA', 'HDMI', 'DisplayPort', 'miniHDMI', 'miniDisplayPort'],
                ],
                'source' => [
                    'source' => 'video_output',
                    'scope' => null,
                    'locale' => 'en_US',
                    'parameters' => [
                        'label_locale' => 'en_US',
                        'default' => null,
                    ],
                ],
            ],
            'non localizable and non scopable attribute' => [
                'attribute' => [
                    'code' => 'video_output',
                    'type' => 'pim_catalog_multiselect',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                    'options' => ['VGA', 'HDMI', 'DisplayPort', 'miniHDMI', 'miniDisplayPort'],
                ],
                'source' => [
                    'source' => 'video_output',
                    'scope' => null,
                    'locale' => null,
                    'parameters' => [
                        'label_locale' => 'en_US',
                        'default' => null,
                    ],
                ],
            ],
            'default value defined' => [
                'attribute' => [
                    'code' => 'video_output',
                    'type' => 'pim_catalog_multiselect',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                    'options' => ['VGA', 'HDMI', 'DisplayPort', 'miniHDMI', 'miniDisplayPort'],
                ],
                'source' => [
                    'source' => 'video_output',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                    'parameters' => [
                        'label_locale' => 'en_US',
                        'default' => 'HDMI',
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
        array $source,
        string $expectedMessage,
    ): void {
        $this->createAttribute($attribute);

        $violations = $this->validator->validate($source, new AttributeMultiSelectSource());

        $this->assertViolationsListContains($violations, $expectedMessage);
    }

    public function invalidDataProvider(): array
    {
        return [
            'invalid source value' => [
                'attribute' => [
                    'code' => 'video_output',
                    'type' => 'pim_catalog_multiselect',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                    'options' => ['VGA', 'HDMI', 'DisplayPort', 'miniHDMI', 'miniDisplayPort'],
                ],
                'source' => [
                    'source' => 42,
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                    'parameters' => [
                        'label_locale' => 'en_US',
                        'default' => null,
                    ],
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'invalid scope' => [
                'attribute' => [
                    'code' => 'video_output',
                    'type' => 'pim_catalog_multiselect',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                    'options' => ['VGA', 'HDMI', 'DisplayPort', 'miniHDMI', 'miniDisplayPort'],
                ],
                'source' => [
                    'source' => 'video_output',
                    'scope' => 42,
                    'locale' => 'en_US',
                    'parameters' => [
                        'label_locale' => 'en_US',
                        'default' => null,
                    ],
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'invalid locale' => [
                'attribute' => [
                    'code' => 'video_output',
                    'type' => 'pim_catalog_multiselect',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                    'options' => ['VGA', 'HDMI', 'DisplayPort', 'miniHDMI', 'miniDisplayPort'],
                ],
                'source' => [
                    'source' => 'video_output',
                    'scope' => 'ecommerce',
                    'locale' => 42,
                    'parameters' => [
                        'label_locale' => 'en_US',
                        'default' => null,
                    ],
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'source with invalid locale for a channel' => [
                'attribute' => [
                    'code' => 'video_output',
                    'type' => 'pim_catalog_multiselect',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                    'options' => ['VGA', 'HDMI', 'DisplayPort', 'miniHDMI', 'miniDisplayPort'],
                ],
                'source' => [
                    'source' => 'video_output',
                    'scope' => 'ecommerce',
                    'locale' => 'kz_KZ',
                    'parameters' => [
                        'label_locale' => 'en_US',
                        'default' => null,
                    ],
                ],
                'expectedMessage' => 'This locale is disabled. Please check your channels and locales settings or update this value.',
            ],
            'source with unknown scope' => [
                'attribute' => [
                    'code' => 'video_output',
                    'type' => 'pim_catalog_multiselect',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => false,
                    'options' => ['VGA', 'HDMI', 'DisplayPort', 'miniHDMI', 'miniDisplayPort'],
                ],
                'source' => [
                    'source' => 'video_output',
                    'scope' => 'unknown_scope',
                    'locale' => null,
                    'parameters' => [
                        'label_locale' => 'en_US',
                        'default' => null,
                    ],
                ],
                'expectedMessage' => 'This channel has been deleted. Please check your channel settings or update this value.',
            ],
            'source with invalid locale' => [
                'attribute' => [
                    'code' => 'video_output',
                    'type' => 'pim_catalog_multiselect',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => true,
                    'options' => ['VGA', 'HDMI', 'DisplayPort', 'miniHDMI', 'miniDisplayPort'],
                ],
                'source' => [
                    'source' => 'video_output',
                    'scope' => null,
                    'locale' => 'kz_KZ',
                    'parameters' => [
                        'label_locale' => 'en_US',
                        'default' => null,
                    ],
                ],
                'expectedMessage' => 'This locale is disabled or does not exist anymore. Please check your channels and locales settings.',
            ],
            'source with missing parameters' => [
                'attribute' => [
                    'code' => 'video_output',
                    'type' => 'pim_catalog_multiselect',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                    'options' => ['VGA', 'HDMI', 'DisplayPort', 'miniHDMI', 'miniDisplayPort'],
                ],
                'source' => [
                    'source' => 'video_output',
                    'scope' => null,
                    'locale' => null,
                ],
                'expectedMessage' => 'This field is missing.',
            ],
            'source with missing scope' => [
                'attribute' => [
                    'code' => 'video_output',
                    'type' => 'pim_catalog_multiselect',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => false,
                    'options' => ['VGA', 'HDMI', 'DisplayPort', 'miniHDMI', 'miniDisplayPort'],
                ],
                'source' => [
                    'source' => 'video_output',
                    'scope' => 'unknown_scope',
                    'locale' => null,
                    'parameters' => [
                        'label_locale' => 'en_US',
                        'default' => null,
                    ],
                ],
                'expectedMessage' => 'This channel has been deleted. Please check your channel settings or update this value.',
            ],
            'source with missing locale' => [
                'attribute' => [
                    'code' => 'video_output',
                    'type' => 'pim_catalog_multiselect',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => true,
                    'options' => ['VGA', 'HDMI', 'DisplayPort', 'miniHDMI', 'miniDisplayPort'],
                ],
                'source' => [
                    'source' => 'video_output',
                    'scope' => null,
                    'locale' => null,
                    'parameters' => [
                        'label_locale' => 'en_US',
                        'default' => null,
                    ],
                ],
                'expectedMessage' => 'This locale must not be empty.',
            ],
            'source with missing label_locale field' => [
                'attribute' => [
                    'code' => 'video_output',
                    'type' => 'pim_catalog_multiselect',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                    'options' => ['VGA', 'HDMI', 'DisplayPort', 'miniHDMI', 'miniDisplayPort'],
                ],
                'source' => [
                    'source' => 'video_output',
                    'scope' => null,
                    'locale' => null,
                    'parameters' => [
                        'default' => null,
                    ],
                ],
                'expectedMessage' => 'This field is missing.',
            ],
            'source with invalid label_locale type' => [
                'attribute' => [
                    'code' => 'video_output',
                    'type' => 'pim_catalog_multiselect',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                    'options' => ['VGA', 'HDMI', 'DisplayPort', 'miniHDMI', 'miniDisplayPort'],
                ],
                'source' => [
                    'source' => 'video_output',
                    'scope' => null,
                    'locale' => null,
                    'parameters' => [
                        'label_locale' => 42,
                        'default' => null,
                    ],
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'source with invalid label_locale locale' => [
                'attribute' => [
                    'code' => 'video_output',
                    'type' => 'pim_catalog_multiselect',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                    'options' => ['VGA', 'HDMI', 'DisplayPort', 'miniHDMI', 'miniDisplayPort'],
                ],
                'source' => [
                    'source' => 'video_output',
                    'scope' => null,
                    'locale' => null,
                    'parameters' => [
                        'label_locale' => 'kz_KZ',
                        'default' => null,
                    ],
                ],
                'expectedMessage' => 'This locale is disabled or does not exist anymore. Please check your channels and locales settings.',
            ],
            'source with missing default field' => [
                'attribute' => [
                    'code' => 'video_output',
                    'type' => 'pim_catalog_multiselect',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                    'options' => ['VGA', 'HDMI', 'DisplayPort', 'miniHDMI', 'miniDisplayPort'],
                ],
                'source' => [
                    'source' => 'video_output',
                    'scope' => null,
                    'locale' => null,
                    'parameters' => [
                        'label_locale' => 'en_US',
                    ],
                ],
                'expectedMessage' => 'This field is missing.',
            ],
            'source with invalid default field' => [
                'attribute' => [
                    'code' => 'video_output',
                    'type' => 'pim_catalog_multiselect',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                    'options' => ['VGA', 'HDMI', 'DisplayPort', 'miniHDMI', 'miniDisplayPort'],
                ],
                'source' => [
                    'source' => 'video_output',
                    'scope' => null,
                    'locale' => null,
                    'parameters' => [
                        'label_locale' => 'en_US',
                        'default' => 10,
                    ],
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'source with extra field' => [
                'attribute' => [
                    'code' => 'size',
                    'type' => 'pim_catalog_multiselect',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'source' => [
                    'source' => 'size',
                    'scope' => null,
                    'locale' => null,
                    'category' => 'dimension',
                    'parameters' => [
                        'label_locale' => 'en_US',
                        'default' => null,
                    ],
                ],
                'expectedMessage' => 'This field was not expected.',
            ],
            'source with extra parameter field' => [
                'attribute' => [
                    'code' => 'size',
                    'type' => 'pim_catalog_multiselect',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'source' => [
                    'source' => 'size',
                    'scope' => null,
                    'locale' => null,
                    'parameters' => [
                        'label_locale' => 'en_US',
                        'category' => 'dimension',
                        'default' => null,
                    ],
                ],
                'expectedMessage' => 'This field was not expected.',
            ],
        ];
    }
}
