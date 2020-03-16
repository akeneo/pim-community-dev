<?php

namespace Akeneo\Tool\Bundle\MeasureBundle\tests\EndToEnd\InternalApi;

use Akeneo\Tool\Bundle\MeasureBundle\Model\LabelCollection;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Operation;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Unit;
use Akeneo\Tool\Bundle\MeasureBundle\Model\UnitCode;
use Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;
use Akeneo\Tool\Bundle\MeasureBundle\tests\EndToEnd\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CreateMeasurementFamilyEndToEnd extends WebTestCase
{
    /** @var MeasurementFamilyRepositoryInterface */
    private $measurementFamilyRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->measurementFamilyRepository = $this->get('akeneo_measure.persistence.measurement_family_repository');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @test
     */
    public function it_creates_a_measurement_family()
    {
        $measurementFamily = self::createMeasurementFamily('custom_metric_1');
        $normalizedMeasurementFamily = $measurementFamily->normalize();

        $this->authenticateAsAdmin();
        $this->client->request(
            'POST',
            'rest/measurement-families',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
            json_encode($normalizedMeasurementFamily)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertMeasurementFamilyHasBeenCreated($measurementFamily);
    }

    /**
     * @test
     */
    public function it_returns_an_error_when_the_measurement_family_does_not_have_the_right_structure()
    {
        $invalidMeasurementFamily = ['values' => null];

        $this->authenticateAsAdmin();
        $this->client->request(
            'POST',
            'rest/measurement-families',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
            json_encode($invalidMeasurementFamily)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $responseBody = json_decode($response->getContent(), true);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $responseBody['code']);
        $this->assertEquals('The measurement family has an invalid format.', $responseBody['message']);
    }

    /**
     * @test
     */
    public function it_returns_an_error_when_the_measurement_family_is_not_valid()
    {
        $measurementFamily = self::createMeasurementFamily('custom_metric_1');
        $normalizedMeasurementFamily = $measurementFamily->normalize();
        $normalizedMeasurementFamily['code'] = 'INVALID CODE WITH SPACES';

        $this->authenticateAsAdmin();
        $this->client->request(
            'POST',
            'rest/measurement-families',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
            json_encode($normalizedMeasurementFamily)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $responseBody = json_decode($response->getContent(), true);
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $responseBody['code']);
        $this->assertEquals(
            'The measurement family has data that does not comply with the business rules.',
            $responseBody['message']
        );
    }

    /**
     * @test
     */
    public function it_returns_an_error_when_the_measurement_family_code_already_exists()
    {
        $measurementFamily = self::createMeasurementFamily('custom_metric_1');
        $normalizedMeasurementFamily = $measurementFamily->normalize();

        $this->authenticateAsAdmin();
        $this->client->request(
            'POST',
            'rest/measurement-families',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
            json_encode($normalizedMeasurementFamily)
        );

        $this->client->restart();

        $this->authenticateAsAdmin();
        $this->client->request(
            'POST',
            'rest/measurement-families',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
            json_encode($normalizedMeasurementFamily)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    private static function createMeasurementFamily(string $code): MeasurementFamily
    {
        return MeasurementFamily::create(
            MeasurementFamilyCode::fromString($code),

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
                    [Operation::create('mul', '0.1')],
                    'cm²'
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
