<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Client\Fos;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\Client;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateClientIntegration extends TestCase
{
    public function test_the_client_creation()
    {
        Assert::assertCount(0, $this->fetchApiClients());

        $client = $this
            ->get('akeneo_connectivity.connection.service.client.create_client')
            ->execute('Magento');
        Assert::assertInstanceOf(Client::class, $client);

        $createdClients = $this->fetchApiClients();
        Assert::assertCount(1, $createdClients);

        $createdClient = $createdClients[0];
        Assert::assertEquals('Magento', $createdClient['label']);
        Assert::assertRegExp('/password/', $createdClient['allowed_grant_types']);
        Assert::assertRegExp('/refresh_token/', $createdClient['allowed_grant_types']);

        Assert::assertEquals($createdClient['id'], $client->id());
        $publicId = sprintf('%s_%s', $createdClient['id'], $createdClient['random_id']);
        Assert::assertEquals($publicId, $client->clientId());
        Assert::assertEquals($createdClient['secret'], $client->secret());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function fetchApiClients(): array
    {
        $sqlQuery = <<<SQL
SELECT id, random_id, secret, label, allowed_grant_types FROM pim_api_client
SQL;

        return $this->getDatabaseConnection()->fetchAll($sqlQuery);
    }

    private function getDatabaseConnection(): Connection
    {
        return $this->get('database_connection');
    }
}
