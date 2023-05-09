<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Connection\Controller\Internal;

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
    public function test_it_deletes_a_connection(): void
    {
        $this->createConnection('franklin', 'Franklin', FlowType::DATA_SOURCE, false);

        $this->authenticateAsAdmin();
        $this->client->request('DELETE', '/rest/connections/franklin');
        $response = $this->client->getResponse();

        Assert::assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        Assert::assertEquals('', $response->getContent());
    }

    public function test_it_fails_to_delete_an_unknown_connection(): void
    {
        $this->authenticateAsAdmin();
        $this->client->request('DELETE', '/rest/connections/unknown_connection');
        $result = \json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

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
