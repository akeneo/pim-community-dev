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
        $this->loadFixtures();
    }

    private function loadFixtures(): void
    {
        $this->fixturesLoader
            ->referenceEntity('country')
            ->load();

        $this->fixturesLoader
            ->referenceEntity('designer')
            ->load();

        $this->fixturesLoader
            ->referenceEntity('brand')
            ->withAttributes([
                'long_description',     // text
                'country',              // record
                'designers',            // record collection
                'main_image',           // image
                'main_material',        // option
                'materials',            // option collection
                'year'                  // number
            ])
            ->load();
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
                'long_description' => [
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
                'main_image' => [
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
                'materials' => [
                    [
                        'locale'  => 'en_US',
                        'channel' => null,
                        'data'    => [
                            'plastic',
                            'wool',
                            'wood',
                        ],
                    ],
                ],
                'year' => [
                    [
                        'locale'  => null,
                        'channel' => null,
                        'data'    => '1949',
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
                'long_description' => [
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
                'main_image' => [
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
                'materials' => [
                    [
                        'locale'  => 'en_US',
                        'channel' => null,
                        'data'    => [
                            'lighting',
                            'home_accessories',
                            null,
                        ],
                    ],
                ],
                'year' => [
                    [
                        'locale'  => null,
                        'channel' => null,
                        'data'    => 1949,
                    ],
                ],
            ],
        ];

        $errors = $this->recordValidator->validate(ReferenceEntityIdentifier::fromString('brand'), $record);
        $errors = JsonSchemaErrorsFormatter::format($errors);

        $this->assertCount(33, $errors);
        $this->assertContains(
            [
                'property' => 'values.country[0].data',
                'message'  => 'The data (integer) must match the type: string, null'
            ],
            $errors
        );
        $this->assertContains(
            [
                'property' => 'values.long_description[1]',
                'message'  => 'The required properties (data) are missing'
            ],
            $errors
        );
        $this->assertContains(
            [
                'property' => 'values.designers[0].data',
                'message'  => 'The data (string) must match the type: array'
            ],
            $errors
        );
        $this->assertContains(
            [
                'property' => 'values.main_material[0]',
                'message'  => 'The required properties (channel) are missing'
            ],
            $errors
        );
        $this->assertContains(
            [
                'property' => 'values.main_image[0]',
                'message'  => 'The required properties (locale) are missing'
            ],
            $errors
        );
        $this->assertContains(
            [
                'property' => 'values.materials[0].data[2]',
                'message'  => 'The data (null) must match the type: string'
            ],
            $errors
        );
        $this->assertContains(
            [
                'property' => 'values.year[0].data',
                'message'  => 'The data (integer) must match the type: string, null'
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

        $this->assertCount(1, $errors);
        $this->assertContains(
            [
                'property' => '',
                'message'  => 'The required properties (code) are missing'
            ],
            $errors
        );
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }
}
