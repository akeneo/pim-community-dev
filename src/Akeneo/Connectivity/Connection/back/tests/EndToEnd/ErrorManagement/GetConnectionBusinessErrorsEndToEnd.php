<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\ErrorManagement;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Test\Integration\Configuration;
use Doctrine\DBAL\Connection as DbalConnection;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetConnectionBusinessErrorsEndToEnd extends WebTestCase
{
    /** @var DbalConnection */
    private $dbalConnection;

    public function test_it_gets_a_connection_business_errors(): void
    {
        $errors = [
            ['erp', '2020-01-01 00:00:00', '{"message": "Error 1"}'],
            ['erp', '2020-01-07 00:00:00', '{"message": "Error 2"}'],
        ];
        $this->insertBusinessErrors($errors);

        $expectedResult = [
            [
                'connection_code' => 'erp',
                'date_time' => '2020-01-07T00:00:00+00:00',
                'content' => ['message' => 'Error 2']
            ],
            [
                'connection_code' => 'erp',
                'date_time' => '2020-01-01T00:00:00+00:00',
                'content' => ['message' => 'Error 1']
            ],
        ];

        $this->authenticateAsAdmin();
        $this->client->request('GET', '/rest/connections/erp/business-errors', [
            'end_date' => '2020-01-07',
        ]);
        $result = json_decode($this->client->getResponse()->getContent(), true);

        Assert::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        Assert::assertEquals($expectedResult, $result);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbalConnection = $this->get('database_connection');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function insertBusinessErrors(array $errors): void
    {
        foreach ($errors as [$connectionCode, $dateTime, $content]) {
            $this->dbalConnection->insert('akeneo_connectivity_connection_audit_business_error', [
                'connection_code' => $connectionCode,
                'error_datetime' => $dateTime,
                'content' => $content
            ]);
        }
    }
}
