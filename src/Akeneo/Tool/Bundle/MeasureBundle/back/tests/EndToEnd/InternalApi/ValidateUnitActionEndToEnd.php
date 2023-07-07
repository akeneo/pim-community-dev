<?php

namespace Akeneo\Tool\Bundle\MeasureBundle\tests\EndToEnd\InternalApi;

use Akeneo\Tool\Bundle\MeasureBundle\Model\LabelCollection;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Operation;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Unit;
use Akeneo\Tool\Bundle\MeasureBundle\Model\UnitCode;
use Akeneo\Tool\Bundle\MeasureBundle\tests\EndToEnd\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ValidateUnitActionEndToEnd extends WebTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->authenticateAsAdmin();
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
    public function it_returns_ok_if_the_new_unit_is_valid()
    {
        $measurementFamilyCode = MeasurementFamilyCode::fromString('Power');
        $unit = Unit::create(
            UnitCode::fromString('CUSTOM_UNIT'),
            LabelCollection::fromArray(['en_US' => 'Custom unit', 'fr_FR' => 'Unité personalisée']),
            [Operation::create('mul', '0.1')],
            'cu'
        );

        $response = $this->requestValidationEndpoint($measurementFamilyCode, $unit);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_returns_validation_errors_if_the_new_unit_is_not_valid()
    {
        $measurementFamilyCode = MeasurementFamilyCode::fromString('Power');
        $unit = Unit::create(
            UnitCode::fromString('WATT'), // "WATT" already exists in Power
            LabelCollection::fromArray(['en_US' => 'Custom unit', 'fr_FR' => 'Unité personalisée']),
            [Operation::create('mul', '0.1')],
            'cu'
        );

        $response = $this->requestValidationEndpoint($measurementFamilyCode, $unit);
        $expectedResponse =
<<<JSON
[
    {
        "messageTemplate": "pim_measurements.validation.unit.code.must_be_unique",
        "parameters": [],
        "plural": null,
        "message": "This unit code already exists.",
        "root": {
        "measurementFamilyCode": "Power",
        "code": "WATT",
        "labels": {
            "en_US": "Custom unit",
            "fr_FR": "Unité personalisée"
        },
        "convert_from_standard": [
            {
                "operator": "mul",
                "value": "0.1"
            }
        ],
        "symbol": "cu"
        },
        "propertyPath": "code",
        "invalidValue": "WATT",
        "constraint": {
        "targets": "class",
        "defaultOption": null,
        "requiredOptions": [],
        "message": "pim_measurements.validation.unit.code.must_be_unique",
        "payload": null,
        "groups": ["other_constraints"]
        },
        "cause": null,
        "code": null
    }
]
JSON;

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertEquals(json_decode($expectedResponse), json_decode($response->getContent()));
    }

    /**
     * @test
     */
    public function it_returns_not_found_if_the_measurement_family_does_not_exist()
    {
        $measurementFamilyCode = MeasurementFamilyCode::fromString('NotFound');
        $unit = Unit::create(
            UnitCode::fromString('CUSTOM_UNIT'),
            LabelCollection::fromArray(['en_US' => 'Custom unit', 'fr_FR' => 'Unité personalisée']),
            [Operation::create('mul', '0.1')],
            'cu'
        );

        $response = $this->requestValidationEndpoint($measurementFamilyCode, $unit);

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    private function requestValidationEndpoint(MeasurementFamilyCode $measurementFamilyCode, Unit $unit)
    {
        $this->client->request(
            'POST',
            sprintf('rest/measurement-families/%s/validate-unit', $measurementFamilyCode->normalize()),
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
            json_encode($unit->normalize())
        );

        return $this->client->getResponse();
    }
}
