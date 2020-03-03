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

class SaveMeasurementFamiliesEndToEnd extends ApiTestCase
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

		$client->request('PATCH', 'api/rest/v1/measurement-families', [], [], [], json_encode($multipleMeasurementFamilies));

		$response = $client->getResponse();
		$this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertMeasurementFamilyHasBeenCreated($this->measurementFamily1());
        $this->assertMeasurementFamilyHasBeenCreated($this->measurementFamily2());
    }

    // Add spec - Add check for structure errors
    // Add spec - Add check the maximum resources to process does not exceed the limit
    // Add spec - Add check the maximum resources to process does not exceed the limit
	// Add spec - Add check if the measurement family is not processable
	// Add spec - Add check if the validator throws a violation http exception

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
					LabelCollection::fromArray(["en_US" => "Custom unit 1_1", "fr_FR" => "Unité personalisée 1_1"]),
					[Operation::create("mul", "0.000001")],
					"mm²",
					),
				Unit::create(
					UnitCode::fromString('CUSTOM_UNIT_2_1'),
					LabelCollection::fromArray(["en_US" => "Custom unit 2_1", "fr_FR" => "Unité personalisée 2_1"]),
					[Operation::create("mul", "0.0001")],
					"cm²",
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
					LabelCollection::fromArray(["en_US" => "Custom unit 1_1", "fr_FR" => "Unité personalisée 1_1"]),
					[Operation::create("mul", "0.000001")],
					"mm²",
					),
				Unit::create(
					UnitCode::fromString('CUSTOM_UNIT_2_1'),
					LabelCollection::fromArray(["en_US" => "Custom unit 2_1", "fr_FR" => "Unité personalisée 2_1"]),
					[Operation::create("mul", "0.0001")],
					"cm²",
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
