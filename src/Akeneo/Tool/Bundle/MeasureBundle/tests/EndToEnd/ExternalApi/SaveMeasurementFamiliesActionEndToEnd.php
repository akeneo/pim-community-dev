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
    /** @var MeasurementFamilyRepositoryInterface */
    private $measurementFamilyRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->measurementFamilyRepository = $this->get('akeneo_measure.persistence.measurement_family_repository');
    }

    /**
     * @test
     */
    public function it_creates_multiple_measurement_families()
    {
        $multipleMeasurementFamilies = [
            $this->measurementFamily1()->normalize(),
            $this->measurementFamily2()->normalize()
        ];
        $client = $this->createAuthenticatedClient();

        $client->request(
            'PATCH',
            'api/rest/v1/measurement-families',
            [],
            [],
            [],
            json_encode($multipleMeasurementFamilies)
        );

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertMeasurementFamilyHasBeenCreated($this->measurementFamily1());
        $this->assertMeasurementFamilyHasBeenCreated($this->measurementFamily2());
    }

    /**
     * @test
     */
    public function it_returns_an_error_when_the_measurement_family_list_does_not_have_the_right_structure()
    {
        $invalidMeasurementFamilyStructure = [
            'values' => null,
        ];
        $client = $this->createAuthenticatedClient();

        $client->request(
            'PATCH',
            'api/rest/v1/measurement-families',
            [],
            [],
            [],
            json_encode($invalidMeasurementFamilyStructure)
        );

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $responseBody = json_decode($response->getContent(), true);
        $this->assertEquals(400, $responseBody['code']);
        $this->assertEquals('The list of measurement families has an invalid format.', $responseBody['message']);
    }

    /**
     * @test
     */
    public function it_returns_an_error_when_the_measurement_family_measurement_does_not_have_the_right_structure()
    {
        $invalidMeasurementFamilyStructure = [
            [
                'code'               => 'custom_metric_1',
                'standard_unit_code' => 'CUSTOM_UNIT_1_1',
                'units'              =>
                    [
                        [
                            'code'                  => 'CUSTOM_UNIT_1_1',
                            'labels'                =>
                                [
                                    'en_US' => 'Custom unit 1_1',
                                    'fr_FR' => 'Unité personalisée 1_1',
                                ],
                            'convert_from_standard' =>
                                [
                                    [
                                        'operator' => 'mul',
                                        'value'    => '0.000001',
                                    ],
                                ],
                            'symbol'                => 'mm²',
                        ],
                        [
                            'code'                  => 'CUSTOM_UNIT_2_1',
                            'labels'                =>
                                [
                                    'en_US' => 'Custom unit 2_1',
                                    'fr_FR' => 'Unité personalisée 2_1',
                                ],
                            'convert_from_standard' =>
                                [
                                    [
                                        'operator' => 'mul',
                                        'value'    => '0.0001',
                                    ],
                                ],
                            'symbol'                => 'cm²',
                        ],
                    ],
            ]
        ];
        $client = $this->createAuthenticatedClient();

        $client->request(
            'PATCH',
            'api/rest/v1/measurement-families',
            [],
            [],
            [],
            json_encode($invalidMeasurementFamilyStructure)
        );

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $responseBody = json_decode($response->getContent(), true);
        $this->assertSame(
            [
                'code'        => 'custom_metric_1',
                'status_code' => 422,
                'message'     => 'The measurement family has an invalid format.',
                'errors'      =>
                    [
                        [
                            'property' => 'labels',
                            'message'  => 'The property labels is required',
                        ],
                    ],
            ],
            current($responseBody)
        );
    }

    // Add spec - Add check the maximum resources to process does not exceed the limit
    // Add test - Add check if the validator throws a violation http exception

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function measurementFamily1(): MeasurementFamily
    {
        return MeasurementFamily::create(
            MeasurementFamilyCode::fromString('custom_metric_1'),

            LabelCollection::fromArray(['en_US' => 'Custom measurement 1', 'fr_FR' => 'Mesure personalisée 1']),
            UnitCode::fromString('CUSTOM_UNIT_1_1'),
            [
                Unit::create(
                    UnitCode::fromString('CUSTOM_UNIT_1_1'),
                    LabelCollection::fromArray(['en_US' => 'Custom unit 1_1', 'fr_FR' => 'Unité personalisée 1_1']),
                    [Operation::create('mul', '1')],
                    'mm²',
                    ),
                Unit::create(
                    UnitCode::fromString('CUSTOM_UNIT_2_1'),
                    LabelCollection::fromArray(['en_US' => 'Custom unit 2_1', 'fr_FR' => 'Unité personalisée 2_1']),
                    [Operation::create('mul', '0.0001')],
                    'cm²',
                    )
            ],
            );
    }

    private function measurementFamily2(): MeasurementFamily
    {
        return MeasurementFamily::create(
            MeasurementFamilyCode::fromString('custom_measurement_2'),

            LabelCollection::fromArray(['en_US' => 'Custom measurement 1', 'fr_FR' => 'Mesure personalisée 1']),
            UnitCode::fromString('CUSTOM_UNIT_1_1'),
            [
                Unit::create(
                    UnitCode::fromString('CUSTOM_UNIT_1_1'),
                    LabelCollection::fromArray(['en_US' => 'Custom unit 1_1', 'fr_FR' => 'Unité personalisée 1_1']),
                    [Operation::create('mul', '1')],
                    'mm²',
                    ),
                Unit::create(
                    UnitCode::fromString('CUSTOM_UNIT_2_1'),
                    LabelCollection::fromArray(['en_US' => 'Custom unit 2_1', 'fr_FR' => 'Unité personalisée 2_1']),
                    [Operation::create('mul', '0.0001')],
                    'cm²',
                    )
            ]
        );
    }

    private function assertMeasurementFamilyHasBeenCreated(MeasurementFamily $expected): void
    {
        $measurementFamilyCode = MeasurementFamilyCode::fromString($expected->normalize()['code']);
        $actual = $this->measurementFamilyRepository->getByCode($measurementFamilyCode);

        $this->assertEquals($expected, $actual);
    }
}
