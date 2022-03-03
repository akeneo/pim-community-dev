<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Webhook\Controller\Internal;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegenerateSecretEndToEnd extends WebTestCase
{
    public function test_it_updates_a_connection_webhook(): void
    {
        $connection = $this->createConnection('magento', 'Magento', FlowType::DATA_SOURCE, false);

        $this->authenticateAsAdmin();
        $this->client->request(
            'GET',
            \sprintf('/rest/connections/%s/webhook/regenerate-secret', $connection->code()),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        Assert::assertEquals(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());
    }

    public function test_it_does_not_update_a_connection_that_does_not_exist(): void
    {
        $this->authenticateAsAdmin();
        $this->client->request(
            'GET',
            '/rest/connections/shopify/webhook/regenerate-secret',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        Assert::assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
