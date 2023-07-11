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

class GetMeasurementFamiliesActionEndToEnd extends ApiTestCase
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
    public function it_returns_the_list_of_measurement_families()
    {
        $this->insertMeasurementFamilyWithEmptyLabels();

        $client = $this->createAuthenticatedClient();
        $client->request('GET', 'api/rest/v1/measurement-families');

        $expected = $this->getExpectedJSON('measurement-families.json');
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function insertMeasurementFamilyWithEmptyLabels()
    {
        $measurementFamily = MeasurementFamily::create(
            MeasurementFamilyCode::fromString('Bitcoin'),
            LabelCollection::fromArray([]),
            UnitCode::fromString('SATOSHI'),
            [
                Unit::create(
                    UnitCode::fromString('SATOSHI'),
                    LabelCollection::fromArray([]),
                    [Operation::create('mul', '1')],
                    'satoshi'
                ),
            ]
        );

        $this->measurementFamilyRepository->save($measurementFamily);
    }

    private function getExpectedJSON(string $expected)
    {
        return file_get_contents(sprintf('%s/Responses/%s', __DIR__, $expected));
    }
}
