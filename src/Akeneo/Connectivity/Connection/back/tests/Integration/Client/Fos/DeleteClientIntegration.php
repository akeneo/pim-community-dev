<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Client\Fos;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteClientIntegration extends TestCase
{
    public function test_the_client_deletion()
    {
        $this->createClient('Pimgento');
        $this->createClient('Magento');
        Assert::assertCount(2, $this->fetchApiClients());

        $this
            ->get('akeneo_connectivity.connection.service.client.delete_client')
            ->execute('Pimgento');

        $createdClients = $this->fetchApiClients();
        Assert::assertCount(1, $createdClients);
        Assert::assertNotEquals('Pimgento', $createdClients[0]['label']);
    }

    public function test_a_client_deletion_with_an_unexisting_client()
    {
        Assert::assertCount(0, $this->fetchApiClients());

        try {
            $this
                ->get('akeneo_connectivity.connection.service.client.delete_client')
                ->execute('Pimgento');
        } catch (\InvalidArgumentException $e) {
            var_dump($e->getMessage());
        }
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createClient(string $label): void
    {
        $this
            ->get('akeneo_connectivity.connection.service.client.create_client')
            ->execute($label);
    }

    private function fetchApiClients(): array
    {
        return $this->getDatabaseConnection()->fetchAll('SELECT label FROM pim_api_client');
    }

    private function getDatabaseConnection(): Connection
    {
        return $this->get('database_connection');
    }
}
