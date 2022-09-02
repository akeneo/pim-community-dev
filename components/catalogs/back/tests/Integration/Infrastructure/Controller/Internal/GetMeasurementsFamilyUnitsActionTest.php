<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @covers \Akeneo\Catalogs\Infrastructure\Controller\Internal\GetMeasurementsFamilyUnitsAction
 */
class GetMeasurementsFamilyUnitsActionTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItGetsMeasurementsFamiliesUnits(): void
    {
        $client = $this->getAuthenticatedInternalApiClient('admin');

        $client->request(
            'GET',
            '/rest/catalogs/measurement-families/Weight/units',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        );

        $response = $client->getResponse();
        Assert::assertEquals(200, $response->getStatusCode());

        $families = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        Assert::assertCount(12, $families);
        Assert::assertArrayHasKey('code', $families[0]);
        Assert::assertArrayHasKey('label', $families[0]);
    }

    public function testItGetsNotFoundResponseWithWrongCode(): void
    {
        $client = $this->getAuthenticatedInternalApiClient('admin');

        $client->request(
            'GET',
            '/rest/catalogs/measurement-families/WrongCode/units',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        );

        $response = $client->getResponse();
        Assert::assertEquals(404, $response->getStatusCode());
    }
}
