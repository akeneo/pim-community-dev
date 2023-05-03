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
class RegenerateConnectionSecretEndToEnd extends WebTestCase
{
    public function test_it_regenerates_a_connection_secret(): void
    {
        $this->createConnection('franklin', 'Franklin', FlowType::DATA_SOURCE, false);

        $this->authenticateAsAdmin();
        $this->client->request('POST', '/rest/connections/franklin/regenerate-secret');
        $response = $this->client->getResponse();

        Assert::assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        Assert::assertEquals('', $response->getContent());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
