<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Settings\Controller\Internal;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsConnectionsNumberLimitReachedActionEndToEnd extends WebTestCase
{
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_returns_connections_limit_reached_flag(): void
    {
        $connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $connectionLoader->createConnection('erp', 'ERP', FlowType::DATA_SOURCE, true);
        $connectionLoader->createConnection('magento', 'Magento', FlowType::DATA_DESTINATION, false, 'app');

        $this->authenticateAsAdmin();

        $this->client->request(
            'GET',
            '/rest/connections/max-limit-reached',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );
        $response = $this->client->getResponse();
        $result = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        Assert::assertEquals(['limitReached' => false], $result);
    }
}
