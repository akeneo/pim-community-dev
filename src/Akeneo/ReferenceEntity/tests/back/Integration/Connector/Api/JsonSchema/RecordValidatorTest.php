<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Integration\Connector\Api\JsonSchema;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRichTextEditor;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\JsonSchemaErrorsFormatter;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Record\JsonSchema\RecordValidator;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;

class RecordValidatorTest extends SqlIntegrationTestCase
{
    /** @var RecordValidator */
    private $recordValidator;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    /** @var int */
    private $attributeOrder;

    public function setUp(): void
    {
        parent::setUp();

        $this->recordValidator = $this->get('akeneo_referenceentity.infrastructure.connector.api.record_validator');
        $this->attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $this->referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $this->attributeOrder = 2;

        $this->resetDB();
        $this->loadReferenceEntity();
        $this->loadTextAttribute();
        $this->loadRecordAttribute();
        $this->loadRecordCollectionAttribute();
        $this->loadImageAttribute();
        $this->loadOptionAttribute();
        $this->loadOptionCollectionAttribute();
    }

    /**
     * @test
     */
    public function it_returns_an_empty_array_if_the_record_structure_is_valid()
    {
        $record = [
            'code' => 'kartell',
            'values' => [
                'label' => [
                    [
                        'locale'  => 'en_US',
                        'channel' => null,
                        'data'    => 'Kartell english label'
                    ]
                ],
                'description' => [
                    [
                        'locale'  => 'en_US',
                        'channel' => 'ecommerce',
                        'data'    => 'This famous Italian company has revolutionised plastic.',
                    ],
                    [
                        'locale'  => 'en_US',
                        'channel' => 'mobile',
                        'data'    => null,
                    ],
                ],
                'country' => [
                    [
                        'locale'  => null,
                        'channel' => null,
                        'data'    => 'italy',
                    ],
                ],
                'designers' => [
                    [
                        'locale'  => null,
                        'channel' => 'ecommerce',
                        'data'    => ['starck', 'arad'],
                    ],
                ],
                'photo' => [
                    [
                        'locale'  => null,
                        'channel' => 'mobile',
                        'data'    => 'images/kartell_small.jpg',
                    ],
                    [
                        'locale'  => null,
                        'channel' => 'ecommerce',
                        'data'    => 'images/kartell_large.jpg',
                    ],
                ],
                'main_material' => [
                    [
                        'locale'  => null,
                        'channel' => null,
                        'data'    => 'plastic',
                    ],
                ],
                'products' => [
                    [
                        'locale'  => null,
                        'channel' => 'ecommerce',
                        'data'    => [
                            'furniture',
                            'lighting',
                            'home_accessories',
                        ],
                    ],
                ],
            ],
        ];

        $errors = $this->recordValidator->validate(ReferenceEntityIdentifier::fromString('brand'), $record);

        $this->assertSame([], $errors);
    }

    /**
     * @test
     */
    public function it_returns_all_the_validation_errors_of_the_record_values()
    {
        $record = [
            'code' => 'kartell',
            'values' => [
                'label' => [
                    [
                        'locale'  => 'en_US',
                        'channel' => null,
                        'data'    => 'Kartell english label'
                    ]
                ],
                'description' => [
                    [
                        'locale'  => 'en_US',
                        'channel' => 'ecommerce',
                        'data'    => 'This famous Italian company has revolutionised plastic.',
                    ],
                    [
                        'locale'  => 'en_US',
                        'channel' => 'mobile',
                    ],
                ],
                'country' => [
                    [
                        'locale'  => null,
                        'channel' => null,
                        'data'    => 22,
                    ],
                ],
                'designers' => [
                    [
                        'locale'  => null,
                        'channel' => 'ecommerce',
                        'data'    => 'starck',
                    ],
                ],
                'photo' => [
                    [
                        'channel' => 'mobile',
                        'data'    => 'images/kartell_small.jpg',
                    ],
                ],
                'main_material' => [
                    [
                        'locale' => null,
                        'data'   => 'plastic',
                    ],
                ],
                'products' => [
                    [
                        'locale'  => null,
                        'channel' => 'ecommerce',
                        'data'    => [
                            'lighting',
                            'home_accessories',
                            null,
                        ],
                    ],
                ],
            ],
        ];

        $errors = $this->recordValidator->validate(ReferenceEntityIdentifier::fromString('brand'), $record);
        $errors = JsonSchemaErrorsFormatter::format($errors);

        $this->assertCount(6, $errors);
        $this->assertContains(
            [
                'property' => 'values.country[0].data',
                'message'  => 'Integer value found, but a string or a null is required'
            ],
            $errors
        );
        $this->assertContains(
            [
                'property' => 'values.description[1].data',
                'message'  => 'The property data is required'
            ],
            $errors
        );
        $this->assertContains(
            [
                'property' => 'values.designers[0].data',
                'message'  => 'String value found, but an array is required'
            ],
            $errors
        );
        $this->assertContains(
            [
                'property' => 'values.main_material[0].channel',
                'message'  => 'The property channel is required'
            ],
            $errors
        );
        $this->assertContains(
            [
                'property' => 'values.photo[0].locale',
                'message'  => 'The property locale is required'
            ],
            $errors
        );
        $this->assertContains(
            [
                'property' => 'values.products[0].data[2]',
                'message'  => 'NULL value found, but a string is required'
            ],
            $errors
        );
    }

    /**
     * @test
     */
    public function it_does_not_validate_values_if_the_main_structure_is_invalid()
    {
        $record = [
            'values' => [
                'foo' => 'bar',
                'description' => [
                    [
                        'locale'  => 'en_US',
                        'channel' => 'mobile',
                    ],
                ],
            ],
        ];
        $errors = $this->recordValidator->validate(ReferenceEntityIdentifier::fromString('brand'), $record);
        $errors = JsonSchemaErrorsFormatter::format($errors);

        $this->assertCount(2, $errors);
        $this->assertContains(
            [
                'property' => 'code',
                'message'  => 'The property code is required'
            ],
            $errors
        );
        $this->assertContains(
            [
                'property' => 'values.foo',
                'message'  => 'String value found, but an array is required'
            ],
            $errors
        );
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function loadReferenceEntity(): void
    {
        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('brand'),
            [
                'fr_FR' => 'Marque',
                'en_US' => 'Brand',
            ],
            Image::createEmpty()
        );

        $this->referenceEntityRepository->create($referenceEntity);
    }

    private function loadTextAttribute()
    {
        $attribute = TextAttribute::createTextarea(
            AttributeIdentifier::create('brand', 'description', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('brand'),
            AttributeCode::fromString('description'),
            LabelCollection::fromArray(['en_US' => 'Description']),
            AttributeOrder::fromInteger($this->attributeOrder++),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(255),
            AttributeIsRichTextEditor::fromBoolean(false)
        );

        $this->attributeRepository->create($attribute);
    }

    private function loadRecordAttribute()
    {
        $attribute = RecordAttribute::create(
            AttributeIdentifier::create('brand', 'country', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('brand'),
            AttributeCode::fromString('country'),
            LabelCollection::fromArray(['fr_FR' => 'Pays', 'en_US' => 'Country']),
            AttributeOrder::fromInteger($this->attributeOrder++),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            ReferenceEntityIdentifier::fromString('country')
        );

        $this->attributeRepository->create($attribute);
    }

    private function loadRecordCollectionAttribute()
    {
        $attribute = RecordCollectionAttribute::create(
            AttributeIdentifier::create('brand', 'designers', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('brand'),
            AttributeCode::fromString('designers'),
            LabelCollection::fromArray(['en_US' => 'Designers']),
            AttributeOrder::fromInteger($this->attributeOrder++),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(false),
            ReferenceEntityIdentifier::fromString('designer')
        );

        $this->attributeRepository->create($attribute);
    }

    private function loadImageAttribute()
    {
        $attribute = ImageAttribute::create(
            AttributeIdentifier::create('brand', 'photo', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('brand'),
            AttributeCode::fromString('photo'),
            LabelCollection::fromArray(['en_US' => 'Cover Image']),
            AttributeOrder::fromInteger($this->attributeOrder++),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::fromString('250.2'),
            AttributeAllowedExtensions::fromList(['jpg'])
        );

        $this->attributeRepository->create($attribute);
    }

    private function loadOptionAttribute()
    {
        $attribute = OptionAttribute::create(
            AttributeIdentifier::create('brand', 'main_material', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('brand'),
            AttributeCode::fromString('main_material'),
            LabelCollection::fromArray(['en_US' => 'Main material']),
            AttributeOrder::fromInteger($this->attributeOrder++),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );

        $this->attributeRepository->create($attribute);
    }

    private function loadOptionCollectionAttribute()
    {
        $attribute = OptionCollectionAttribute::create(
            AttributeIdentifier::create('brand', 'products', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('brand'),
            AttributeCode::fromString('products'),
            LabelCollection::fromArray(['en_US' => 'Products']),
            AttributeOrder::fromInteger($this->attributeOrder++),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );

        $this->attributeRepository->create($attribute);
    }
}
