<?php
declare(strict_types=1);

namespace Akeneo\Apps\back\tests\Integration\Service;

use Akeneo\Apps\Application\Service\CreateClientInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

class CreateClientIntegration extends TestCase
{
    /** @var Connection */
    private $dbal;

    /** @var CreateClientInterface */
    private $createClient;

    public function test_that_it_creates_an_app()
    {
        $clientId = $this->createClient->execute('Magento connector');
        $query = <<<SQL
    SELECT client.label, client.secret, client.random_id, client.allowed_grant_types
    FROM pim_api_client as client
    WHERE client.id = :id
SQL;
        $statement = $this->dbal->executeQuery($query, ['id' => $clientId->id()]);
        $result = $statement->fetch();

        Assert::assertSame('Magento connector', $result['label']);
        Assert::assertNotEmpty($result['secret']);
        Assert::assertNotEmpty($result['random_id']);
        Assert::assertSame(['password', 'refresh_token'], unserialize($result['allowed_grant_types']));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->dbal = $this->get('database_connection');
        $this->createClient = $this->get('akeneo_app.service.client.create_client');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
