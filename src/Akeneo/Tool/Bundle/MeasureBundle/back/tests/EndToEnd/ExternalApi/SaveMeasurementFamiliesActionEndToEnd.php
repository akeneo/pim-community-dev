<?php

namespace Akeneo\Tool\Bundle\MeasureBundle\tests\EndToEnd\ExternalApi;

use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Akeneo\Tool\Bundle\MeasureBundle\Model\LabelCollection;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Operation;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Unit;
use Akeneo\Tool\Bundle\MeasureBundle\Model\UnitCode;
use Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;

class SaveMeasurementFamiliesActionEndToEnd extends ApiTestCase
{
    private ?MeasurementFamilyRepositoryInterface $measurementFamilyRepository = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->measurementFamilyRepository = $this->get('akeneo_measure.persistence.measurement_family_repository');
    }

    /**
     * @test
     */
    public function it_create_a_measurement_family_when_it_does_not_exists()
    {
        $measurementFamily = MeasurementFamily::create(
            MeasurementFamilyCode::fromString('custom_metric_1'),
            LabelCollection::fromArray(['en_US' => 'Custom measurement 1', 'fr_FR' => 'Mesure personalisée 1']),
            UnitCode::fromString('CUSTOM_UNIT_1_1'),
            [
                Unit::create(
                    UnitCode::fromString('CUSTOM_UNIT_1_1'),
                    LabelCollection::fromArray(['en_US' => 'Custom unit 1_1', 'fr_FR' => 'Unité personalisée 1_1']),
                    [Operation::create('mul', '1')],
                    'mm²'
                ),
                Unit::create(
                    UnitCode::fromString('CUSTOM_UNIT_2_1'),
                    LabelCollection::fromArray(['en_US' => 'Custom unit 2_1', 'fr_FR' => 'Unité personalisée 2_1']),
                    [Operation::create('mul', '0.0001')],
                    'cm²'
                )
            ]
        );

        $response = $this->request([$measurementFamily->normalizeWithIndexedUnits()]);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame([
            ['code' => 'custom_metric_1', 'status_code' => 201],
        ], json_decode($response->getContent(), true));
        $this->assertMeasurementFamilyIsPersisted($measurementFamily);
    }

    /**
     * @test
     */
    public function it_returns_an_error_when_the_measurement_family_does_not_have_a_code()
    {
        $response = $this->request([
            [
                'labels' => [
                    'es_ES' => 'Embalaje',
                    'fi_FI' => 'Pakkaus',
                    'fr_FR' => 'Emballage',
                ],
            ]
        ]);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame([
            [
                'code' => '',
                'status_code' => 422,
                'message' => 'The measurement family has an invalid format.',
                'errors' => [
                    [
                        'property' => '',
                        'message' => 'The required properties (code) are missing',
                    ]
                ],
            ]
        ], json_decode($response->getContent(), true));
    }

    /**
     * @test
     */
    public function it_returns_an_error_when_the_units_are_not_correctly_indexed()
    {
        $measurementFamily = MeasurementFamily::create(
            MeasurementFamilyCode::fromString('custom_metric_1'),
            LabelCollection::fromArray(['en_US' => 'Custom measurement 1', 'fr_FR' => 'Mesure personalisée 1']),
            UnitCode::fromString('CUSTOM_UNIT_1_1'),
            [
                Unit::create(
                    UnitCode::fromString('CUSTOM_UNIT_1_1'),
                    LabelCollection::fromArray(['en_US' => 'Custom unit 1_1', 'fr_FR' => 'Unité personalisée 1_1']),
                    [Operation::create('mul', '1')],
                    'mm²'
                ),
                Unit::create(
                    UnitCode::fromString('CUSTOM_UNIT_2_1'),
                    LabelCollection::fromArray(['en_US' => 'Custom unit 2_1', 'fr_FR' => 'Unité personalisée 2_1']),
                    [Operation::create('mul', '0.0001')],
                    'cm²'
                )
            ]
        );

        $this->measurementFamilyRepository->save($measurementFamily);

        $response = $this->request([
            [
                'code' => 'custom_metric_1',
                'units' => [
                    'CUSTOM_UNIT_2_1' => [
                        'code' => 'SOME_OTHER_UNIT_CODE',
                        'labels' => ['en_US' => 'Some other unit'],
                        'convert_from_standard' => [
                            [
                                'operator' => 'mul',
                                'value' => '0.00001',
                            ],
                        ],
                        'symbol' => 'O',
                    ],
                ],
            ],
        ]);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame([
            [
                'code' => 'custom_metric_1',
                'status_code' => 422,
                'message' => 'The measurement family has data that does not comply with the business rules.',
                'errors' => [
                    [
                        'property' => '[units][CUSTOM_UNIT_2_1]',
                        'message' => 'The index does not match the unit code.',
                    ]
                ],
            ]
        ], json_decode($response->getContent(), true));
    }

    /**
     * @test
     */
    public function it_does_nothing_when_the_measurement_family_already_exists_and_is_exactly_the_same()
    {
        $measurementFamily = MeasurementFamily::create(
            MeasurementFamilyCode::fromString('custom_metric_1'),
            LabelCollection::fromArray(['en_US' => 'Custom measurement 1', 'fr_FR' => 'Mesure personalisée 1']),
            UnitCode::fromString('CUSTOM_UNIT_1_1'),
            [
                Unit::create(
                    UnitCode::fromString('CUSTOM_UNIT_1_1'),
                    LabelCollection::fromArray(['en_US' => 'Custom unit 1_1', 'fr_FR' => 'Unité personalisée 1_1']),
                    [Operation::create('mul', '1')],
                    'mm²'
                ),
                Unit::create(
                    UnitCode::fromString('CUSTOM_UNIT_2_1'),
                    LabelCollection::fromArray(['en_US' => 'Custom unit 2_1', 'fr_FR' => 'Unité personalisée 2_1']),
                    [Operation::create('mul', '0.0001')],
                    'cm²'
                )
            ]
        );

        $this->measurementFamilyRepository->save($measurementFamily);

        $response = $this->request([$measurementFamily->normalizeWithIndexedUnits()]);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame([
            ['code' => 'custom_metric_1', 'status_code' => 204],
        ], json_decode($response->getContent(), true));
        $this->assertMeasurementFamilyIsPersisted($measurementFamily);
    }

    /**
     * @test
     */
    public function it_add_an_unit_when_it_does_not_exist()
    {
        $measurementFamily = MeasurementFamily::create(
            MeasurementFamilyCode::fromString('custom_metric_1'),
            LabelCollection::fromArray(['en_US' => 'Custom measurement 1', 'fr_FR' => 'Mesure personalisée 1']),
            UnitCode::fromString('CUSTOM_UNIT_1_1'),
            [
                Unit::create(
                    UnitCode::fromString('CUSTOM_UNIT_1_1'),
                    LabelCollection::fromArray(['en_US' => 'Custom unit 1_1', 'fr_FR' => 'Unité personalisée 1_1']),
                    [Operation::create('mul', '1')],
                    'mm²'
                ),
                Unit::create(
                    UnitCode::fromString('CUSTOM_UNIT_2_1'),
                    LabelCollection::fromArray(['en_US' => 'Custom unit 2_1', 'fr_FR' => 'Unité personalisée 2_1']),
                    [Operation::create('mul', '0.0001')],
                    'cm²'
                )
            ]
        );

        $this->measurementFamilyRepository->save($measurementFamily);

        $response = $this->request([
            [
                'code' => 'custom_metric_1',
                'units' => [
                    'CUSTOM_UNIT_3_1' => [
                        'code' => 'CUSTOM_UNIT_3_1',
                        'labels' => [
                            'ca_ES' => 'Centímetre quadrat'
                        ],
                        'convert_from_standard' => [
                            [
                                'operator' => 'mul',
                                'value' => '0.00001'
                            ]
                        ],
                        'symbol' => 'km²'
                    ],
                ]
            ]
        ]);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame([
            ['code' => 'custom_metric_1', 'status_code' => 204],
        ], json_decode($response->getContent(), true));
        $this->assertMeasurementFamilyIsPersisted(MeasurementFamily::create(
            MeasurementFamilyCode::fromString('custom_metric_1'),
            LabelCollection::fromArray(['en_US' => 'Custom measurement 1', 'fr_FR' => 'Mesure personalisée 1']),
            UnitCode::fromString('CUSTOM_UNIT_1_1'),
            [
                Unit::create(
                    UnitCode::fromString('CUSTOM_UNIT_1_1'),
                    LabelCollection::fromArray(['en_US' => 'Custom unit 1_1', 'fr_FR' => 'Unité personalisée 1_1']),
                    [Operation::create('mul', '1')],
                    'mm²'
                ),
                Unit::create(
                    UnitCode::fromString('CUSTOM_UNIT_2_1'),
                    LabelCollection::fromArray(['en_US' => 'Custom unit 2_1', 'fr_FR' => 'Unité personalisée 2_1']),
                    [Operation::create('mul', '0.0001')],
                    'cm²'
                ),
                Unit::create(
                    UnitCode::fromString('CUSTOM_UNIT_3_1'),
                    LabelCollection::fromArray(['ca_ES' => 'Centímetre quadrat']),
                    [Operation::create('mul', '0.00001')],
                    'km²'
                )
            ]
        ));
    }

    /**
     * @test
     */
    public function it_add_a_label_translation()
    {
        $measurementFamily = MeasurementFamily::create(
            MeasurementFamilyCode::fromString('custom_metric_1'),
            LabelCollection::fromArray(['en_US' => 'Custom measurement 1']),
            UnitCode::fromString('CUSTOM_UNIT_1_1'),
            [
                Unit::create(
                    UnitCode::fromString('CUSTOM_UNIT_1_1'),
                    LabelCollection::fromArray(['en_US' => 'Custom unit 1_1']),
                    [Operation::create('mul', '1')],
                    'mm²'
                ),
            ]
        );

        $this->measurementFamilyRepository->save($measurementFamily);
        $response = $this->request([
            [
                'code' => 'custom_metric_1',
                'labels' => ['fr_FR' => 'Mesure personalisée 1'],
            ]
        ]);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame([
            ['code' => 'custom_metric_1', 'status_code' => 204],
        ], json_decode($response->getContent(), true));
        $this->assertMeasurementFamilyIsPersisted(MeasurementFamily::create(
            MeasurementFamilyCode::fromString('custom_metric_1'),
            LabelCollection::fromArray(['en_US' => 'Custom measurement 1', 'fr_FR' => 'Mesure personalisée 1']),
            UnitCode::fromString('CUSTOM_UNIT_1_1'),
            [
                Unit::create(
                    UnitCode::fromString('CUSTOM_UNIT_1_1'),
                    LabelCollection::fromArray(['en_US' => 'Custom unit 1_1']),
                    [Operation::create('mul', '1')],
                    'mm²'
                ),
            ]
        ));
    }

    /**
     * @test
     */
    public function it_update_a_label_translation()
    {
        $measurementFamily = MeasurementFamily::create(
            MeasurementFamilyCode::fromString('custom_metric_1'),
            LabelCollection::fromArray(['en_US' => 'Custom measurement 1']),
            UnitCode::fromString('CUSTOM_UNIT_1_1'),
            [
                Unit::create(
                    UnitCode::fromString('CUSTOM_UNIT_1_1'),
                    LabelCollection::fromArray(['en_US' => 'Custom unit 1_1']),
                    [Operation::create('mul', '1')],
                    'mm²'
                ),
            ]
        );

        $this->measurementFamilyRepository->save($measurementFamily);
        $response = $this->request([
            [
                'code' => 'custom_metric_1',
                'labels' => ['en_US' => 'Custom measurement 2'],
            ]
        ]);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame([
            ['code' => 'custom_metric_1', 'status_code' => 204],
        ], json_decode($response->getContent(), true));
        $this->assertMeasurementFamilyIsPersisted(MeasurementFamily::create(
            MeasurementFamilyCode::fromString('custom_metric_1'),
            LabelCollection::fromArray(['en_US' => 'Custom measurement 2']),
            UnitCode::fromString('CUSTOM_UNIT_1_1'),
            [
                Unit::create(
                    UnitCode::fromString('CUSTOM_UNIT_1_1'),
                    LabelCollection::fromArray(['en_US' => 'Custom unit 1_1']),
                    [Operation::create('mul', '1')],
                    'mm²'
                ),
            ]
        ));
    }

    /**
     * @test
     */
    public function it_update_an_unit_operations()
    {
        $measurementFamily = MeasurementFamily::create(
            MeasurementFamilyCode::fromString('custom_metric_1'),
            LabelCollection::fromArray(['en_US' => 'Custom measurement 1']),
            UnitCode::fromString('CUSTOM_UNIT_1_1'),
            [
                Unit::create(
                    UnitCode::fromString('CUSTOM_UNIT_1_1'),
                    LabelCollection::fromArray(['en_US' => 'Custom unit 1_1']),
                    [Operation::create('mul', '1')],
                    'mm²'
                ),
                Unit::create(
                    UnitCode::fromString('CUSTOM_UNIT_2_1'),
                    LabelCollection::fromArray(['en_US' => 'Custom unit 2_1']),
                    [Operation::create('mul', '0.0001')],
                    'cm²'
                ),
            ]
        );

        $this->measurementFamilyRepository->save($measurementFamily);

        $response = $this->request([
            [
                'code' => 'custom_metric_1',
                'units' => [
                    'CUSTOM_UNIT_2_1' => [
                        'convert_from_standard' => [
                            [
                                'operator' => 'mul',
                                'value' => '0.1'
                            ],
                            [
                                'operator' => 'add',
                                'value' => '10'
                            ],
                        ],
                    ]
                ]
            ]
        ]);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame([
            ['code' => 'custom_metric_1', 'status_code' => 204],
        ], json_decode($response->getContent(), true));

        $this->assertMeasurementFamilyIsPersisted(MeasurementFamily::create(
            MeasurementFamilyCode::fromString('custom_metric_1'),
            LabelCollection::fromArray(['en_US' => 'Custom measurement 1']),
            UnitCode::fromString('CUSTOM_UNIT_1_1'),
            [
                Unit::create(
                    UnitCode::fromString('CUSTOM_UNIT_1_1'),
                    LabelCollection::fromArray(['en_US' => 'Custom unit 1_1']),
                    [Operation::create('mul', '1')],
                    'mm²'
                ),
                Unit::create(
                    UnitCode::fromString('CUSTOM_UNIT_2_1'),
                    LabelCollection::fromArray(['en_US' => 'Custom unit 2_1']),
                    [
                        Operation::create('mul', '0.1'),
                        Operation::create('add', '10'),
                    ],
                    'cm²'
                ),
            ]
        ));
    }

    /**
     * @test
     */
    public function it_creates_multiple_measurement_families()
    {
        $measurementFamilies = [
            MeasurementFamily::create(
                MeasurementFamilyCode::fromString('custom_metric_1'),
                LabelCollection::fromArray(['en_US' => 'Custom measurement 1', 'fr_FR' => 'Mesure personalisée 1']),
                UnitCode::fromString('CUSTOM_UNIT_1_1'),
                [
                    Unit::create(
                        UnitCode::fromString('CUSTOM_UNIT_1_1'),
                        LabelCollection::fromArray(['en_US' => 'Custom unit 1_1', 'fr_FR' => 'Unité personalisée 1_1']),
                        [Operation::create('mul', '1')],
                        'mm²'
                    ),
                    Unit::create(
                        UnitCode::fromString('CUSTOM_UNIT_2_1'),
                        LabelCollection::fromArray(['en_US' => 'Custom unit 2_1', 'fr_FR' => 'Unité personalisée 2_1']),
                        [Operation::create('mul', '0.0001')],
                        'cm²'
                    )
                ]
            ),
            MeasurementFamily::create(
                MeasurementFamilyCode::fromString('custom_metric_2'),
                LabelCollection::fromArray(['en_US' => 'Custom measurement 1', 'fr_FR' => 'Mesure personalisée 1']),
                UnitCode::fromString('CUSTOM_UNIT_3_1'),
                [
                    Unit::create(
                        UnitCode::fromString('CUSTOM_UNIT_3_1'),
                        LabelCollection::fromArray(['en_US' => 'Custom unit 1_1', 'fr_FR' => 'Unité personalisée 1_1']),
                        [Operation::create('mul', '1')],
                        'mm²'
                    ),
                    Unit::create(
                        UnitCode::fromString('CUSTOM_UNIT_3_2'),
                        LabelCollection::fromArray(['en_US' => 'Custom unit 2_1', 'fr_FR' => 'Unité personalisée 2_1']),
                        [Operation::create('mul', '0.0001')],
                        'cm²'
                    )
                ]
            )
        ];

        $response = $this->request(array_map(static fn (MeasurementFamily $measurementFamily) => $measurementFamily->normalizeWithIndexedUnits(), $measurementFamilies));

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame([
            ['code' => 'custom_metric_1', 'status_code' => 201],
            ['code' => 'custom_metric_2', 'status_code' => 201],
        ], json_decode($response->getContent(), true));

        foreach ($measurementFamilies as $measurementFamily) {
            $this->assertMeasurementFamilyIsPersisted($measurementFamily);
        }
    }

    /**
     * @test
     */
    public function it_returns_an_error_when_the_measurement_family_list_does_not_have_the_right_structure()
    {
        $response = $this->request(['values' => null]);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertSame(
            [
                'code' => 400,
                'message' => 'The list of measurement families has an invalid format.',
                'errors' => [
                    [
                        'property' => '',
                        'message' => 'The data (object) must match the type: array',
                    ],
                ]
            ],
            json_decode($response->getContent(), true)
        );
    }

    /**
     * @test
     */
    public function it_returns_an_error_when_the_measurement_family_creation_does_not_have_the_right_structure()
    {
        $response = $this->request([
            [
                'code' => 'custom_metric_1',
            ]
        ]);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame([
            [
                'code' => 'custom_metric_1',
                'status_code' => 422,
                'message' => 'The measurement family has an invalid format.',
                'errors' =>
                    [
                        [
                            'property' => '',
                            'message' => 'The required properties (units, standard_unit_code) are missing'
                        ]
                    ],
            ]
        ], json_decode($response->getContent(), true));
    }

    /**
     * @test
     */
    public function it_returns_an_error_when_the_measurement_family_update_does_not_have_the_right_structure()
    {
        $measurementFamily = MeasurementFamily::create(
            MeasurementFamilyCode::fromString('custom_metric_1'),
            LabelCollection::fromArray(['en_US' => 'Custom measurement 1', 'fr_FR' => 'Mesure personalisée 1']),
            UnitCode::fromString('CUSTOM_UNIT_1_1'),
            [
                Unit::create(
                    UnitCode::fromString('CUSTOM_UNIT_1_1'),
                    LabelCollection::fromArray(['en_US' => 'Custom unit 1_1', 'fr_FR' => 'Unité personalisée 1_1']),
                    [Operation::create('mul', '1')],
                    'mm²'
                ),
                Unit::create(
                    UnitCode::fromString('CUSTOM_UNIT_2_1'),
                    LabelCollection::fromArray(['en_US' => 'Custom unit 2_1', 'fr_FR' => 'Unité personalisée 2_1']),
                    [Operation::create('mul', '0.0001')],
                    'cm²'
                )
            ]
        );

        $this->measurementFamilyRepository->save($measurementFamily);

        $response = $this->request([
            [
                'code' => 'custom_metric_1',
                'foo' => 'bar'
            ]
        ]);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame([
            [
                'code' => 'custom_metric_1',
                'status_code' => 422,
                'message' => 'The measurement family has an invalid format.',
                'errors' =>
                    [
                        [
                            'property' => '',
                            'message' => 'Additional object properties are not allowed: foo',
                        ],
                    ],
            ]
        ], json_decode($response->getContent(), true));
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function request(array $measurementFamilies): Response
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'PATCH',
            'api/rest/v1/measurement-families',
            [],
            [],
            [],
            json_encode($measurementFamilies)
        );

        return $client->getResponse();
    }

    private function assertMeasurementFamilyIsPersisted(MeasurementFamily $expected): void
    {
        $this->measurementFamilyRepository->clear();

        $measurementFamilyCode = MeasurementFamilyCode::fromString($expected->normalize()['code']);
        $actual = $this->measurementFamilyRepository->getByCode($measurementFamilyCode);

        $this->assertEquals($expected, $actual);
    }
}
