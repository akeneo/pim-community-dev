<?php

namespace Akeneo\Tool\Bundle\MeasureBundle\tests\EndToEnd\InternalApi;

use Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasurementFamilyNotFoundException;
use Akeneo\Tool\Bundle\MeasureBundle\Model\LabelCollection;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Operation;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Unit;
use Akeneo\Tool\Bundle\MeasureBundle\Model\UnitCode;
use Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;
use Akeneo\Tool\Bundle\MeasureBundle\tests\EndToEnd\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class DeleteMeasurementFamilyEndToEnd extends WebTestCase
{
    private ?MeasurementFamilyRepositoryInterface $measurementFamilyRepository = null;

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
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @test
     */
    public function it_deletes_a_measurement_family()
    {
        $measurementFamilyCode = 'custom_metric_1';
        $measurementFamily = $this->createMeasurementFamily($measurementFamilyCode);
        $this->insertMeasurementFamily($measurementFamily);

        $this->assertMeasurementFamilyExists($measurementFamily);

        $this->authenticateAsAdmin();
        $response = $this->requestDeleteEndpoint($measurementFamilyCode);

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertMeasurementFamilyHasBeenDeleted($measurementFamily);
    }

    /**
     * @test
     */
    public function it_returns_an_error_when_the_measurement_family_does_not_exist()
    {
        $measurementFamilyCode = 'custom_metric_1';

        $this->authenticateAsAdmin();
        $response = $this->requestDeleteEndpoint($measurementFamilyCode);

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_returns_an_error_when_the_measurement_family_is_used_by_attributes()
    {
        $measurementFamilyCode = 'Power';

        $this->authenticateAsAdmin();
        $response = $this->requestDeleteEndpoint($measurementFamilyCode);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    private function createMeasurementFamily(string $code): MeasurementFamily
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

    private function requestDeleteEndpoint(string $measurementFamilyCode): Response
    {
        $this->client->request(
            'DELETE',
            sprintf('rest/measurement-families/%s', $measurementFamilyCode),
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );

        return $this->client->getResponse();
    }

    private function insertMeasurementFamily(MeasurementFamily $measurementFamily)
    {
        $this->measurementFamilyRepository->save($measurementFamily);
    }

    private function assertMeasurementFamilyExists(MeasurementFamily $expected): void
    {
        $measurementFamilyCode = MeasurementFamilyCode::fromString($expected->normalize()['code']);
        $actual = $this->measurementFamilyRepository->getByCode($measurementFamilyCode);

        $this->assertEquals($expected, $actual);
    }

    private function assertMeasurementFamilyHasBeenDeleted(MeasurementFamily $expected): void
    {
        $measurementFamilyCode = MeasurementFamilyCode::fromString($expected->normalize()['code']);

        $this->expectException(MeasurementFamilyNotFoundException::class);
        $this->measurementFamilyRepository->getByCode($measurementFamilyCode);
    }
}
