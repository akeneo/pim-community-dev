<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Connection;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DeleteConnectionEndToEnd extends WebTestCase
{
    public function test_it_deletes_a_connection()
    {
        $this->createConnection('franklin', 'Franklin', FlowType::DATA_SOURCE);

        $this->authenticateAsAdmin();
        $this->client->request('DELETE', '/rest/connections/franklin');
        $result = json_decode($this->client->getResponse()->getContent(), true);

        $expectedResult = null;

        Assert::assertEquals(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());
        Assert::assertEquals($expectedResult, $result);
    }

    public function test_it_fails_to_delete_an_unknown_connection()
    {
        $this->authenticateAsAdmin();
        $this->client->request('DELETE', '/rest/connections/unknown_connection');
        $result = json_decode($this->client->getResponse()->getContent(), true);

        $expectedResult = [
            'message' => 'Connection with code "unknown_connection" does not exist'
        ];

        Assert::assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        Assert::assertEquals($expectedResult, $result);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
