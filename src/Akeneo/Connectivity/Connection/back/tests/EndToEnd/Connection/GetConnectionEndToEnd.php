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
class GetConnectionEndToEnd extends WebTestCase
{
    public function test_it_gets_a_connection(): void
    {
        $connection = $this->createConnection('franklin', 'Franklin', FlowType::DATA_SOURCE);

        $this->authenticateAsAdmin();
        $this->client->request('GET', '/rest/connections/franklin');
        $result = json_decode($this->client->getResponse()->getContent(), true);

        $expectedResult = array_merge($connection->normalize(), ['password' => null]);

        Assert::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        Assert::assertEquals($expectedResult, $result);
    }

    public function test_it_fails_to_get_an_unknown_connection(): void
    {
        $this->authenticateAsAdmin();
        $this->client->request('GET', '/rest/connections/unknown_connection');
        $result = json_decode($this->client->getResponse()->getContent(), true);

        $expectedResult = [];

        Assert::assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
        Assert::assertEquals($expectedResult, $result);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
