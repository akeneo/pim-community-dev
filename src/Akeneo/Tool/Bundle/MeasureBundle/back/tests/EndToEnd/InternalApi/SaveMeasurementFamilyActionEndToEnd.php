<?php

declare(strict_types=1);

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

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SaveMeasurementFamilyActionEndToEnd extends WebTestCase
{
    private ?MeasurementFamilyRepositoryInterface $measurementFamilyRepository = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->measurementFamilyRepository = $this->get('akeneo_measure.persistence.measurement_family_repository');
        $this->authenticateAsAdmin();
    }
    /**
     * @test
     */
    public function it_saves_a_measurement_family()
    {
        $measurementFamily = $this->measurementFamily();

        $response = $this->saveMeasurementFamilies($measurementFamily);

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertMeasurementFamilyHasBeenSaved($measurementFamily);
    }

    /**
     * @test
     */
    public function it_cannot_save_the_measurement_family()
    {
        $measurementFamily = $this->invalidMeasurementFamilyWithLabelTooLong();

        $response = $this->saveMeasurementFamilies($measurementFamily);

        $this->assertMeasurementFamilyCannotBeSavedBecauseLabelWasTooLong($response);
        $this->assertMeasurementFamilyHasNotBeenSaved($measurementFamily);
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_the_code_provided_in_the_route_is_different_from_the_body()
    {
        $requestBody = ['code' => 'measurement_family_code'];
        $measurementFamilyCodeInUrlParameter = 'another_measurement_family_code';

        $response = $this->saveWithMeasurementFamilyCodeDifferentFromRouteAndBody(
            $measurementFamilyCodeInUrlParameter,
            $requestBody
        );

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('"The identifier provided in the route and the one given in the body of the request are different"', $response->getContent());
    }

    /**
     * @test
     */
    public function it_redirects_if_not_xmlhttp_request(): void
    {
        $this->client->followRedirects(false);
        $this->client->request(
            'POST',
            'rest/measurement-families/measurement_family_code',
            [],
            [],
            [],
            '[]'
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
    }

    private function assertMeasurementFamilyHasBeenSaved(array $normalizedExpected): void
    {
        $measurementFamilyCode = MeasurementFamilyCode::fromString($normalizedExpected['code']);
        $normalizedActual = $this->measurementFamilyRepository->getByCode($measurementFamilyCode)->normalize();

        $this->assertEquals($normalizedExpected, $normalizedActual);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function saveMeasurementFamilies(array $normalizedMeasurementFamily): Response
    {
        $this->client->request(
            'POST',
            sprintf('rest/measurement-families/%s', $normalizedMeasurementFamily['code']),
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
            json_encode($normalizedMeasurementFamily)
        );

        return $this->client->getResponse();
    }

    private function measurementFamily(): array
    {
        return MeasurementFamily::create(
            MeasurementFamilyCode::fromString('custom_metric'),
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
        )->normalize();
    }

    private function invalidMeasurementFamilyWithLabelTooLong(): array
    {
        $normalizedMeasurementFamily = $this->measurementFamily();
        $invalidMeasurementFamily = $normalizedMeasurementFamily;
        $invalidMeasurementFamily['labels']['fr_FR'] = str_repeat('a', 101);

        return $invalidMeasurementFamily;
    }

    private function assertMeasurementFamilyHasNotBeenSaved(array $normalizedMeasurementFamily)
    {
        $hasNotBeenCreated = false;
        $measurementFamilyCode = MeasurementFamilyCode::fromString($normalizedMeasurementFamily['code']);
        try {
            $normalizedActual = $this->measurementFamilyRepository->getByCode($measurementFamilyCode)->normalize();
        } catch (MeasurementFamilyNotFoundException $e) {
            $hasNotBeenCreated = true;
        }

        self::assertTrue($hasNotBeenCreated, 'Expected the measurement family to not be created, but found it.');
    }

    private function assertMeasurementFamilyCannotBeSavedBecauseLabelWasTooLong(Response $response): void
    {
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $responseBody = json_decode($response->getContent(), true);
        $this->assertEquals('labels[fr_FR]', $responseBody[0]['propertyPath']);
        $this->assertEquals('This value is too long. It should have 100 characters or less.', $responseBody[0]['message']);
    }

    private function saveWithMeasurementFamilyCodeDifferentFromRouteAndBody(
        string $measurementFamilyCodeInUrlParameter,
        array $requestBody
    ): Response {
        $this->client->request(
            'POST',
            sprintf('rest/measurement-families/%s', $measurementFamilyCodeInUrlParameter),
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
            json_encode($requestBody)
        );

        return $this->client->getResponse();
    }
}
