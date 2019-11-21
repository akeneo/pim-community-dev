<?php

declare(strict_types=1);

namespace Akeneo\Apps\back\tests\Integration\Client\Fos;

use Akeneo\Apps\Application\Service\DeleteClientInterface;
use Akeneo\Apps\Domain\Model\ValueObject\ClientId;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DeleteClientIntegration extends TestCase
{
    /** @var Connection */
    private $dbalConnection;

    /** @var DeleteClientInterface */
    private $deleteClient;

    public function test_that_it_deletes_a_client()
    {
        $label = uniqid();
        $clientId = $this->createClient($label);

        $this->deleteClient->execute($clientId);

        $count = $this->countClient($clientId);

        Assert::assertEquals(0, $count);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbalConnection = $this->get('database_connection');
        $this->deleteClient = $this->get('akeneo_app.service.client.delete_client');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createClient(string $label): ClientId
    {
        $insertSql = <<<SQL
    INSERT INTO pim_api_client (random_id, redirect_uris, secret, allowed_grant_types, label)
    VALUES ('123', 'a:0:{}', '', 'a:0:{}', :label)
SQL;

        $this->dbalConnection->executeQuery(
            $insertSql,
            [
                'label' => $label
            ]
        );

        $selectSql = <<<SQL
    SELECT id
    FROM pim_api_client
    WHERE label = :label
SQL;

        $stmt = $this->dbalConnection->executeQuery(
            $selectSql,
            [
                'label' => $label
            ]
        );

        $id = $stmt->fetchColumn();

        return new ClientId((int) $id);
    }

    private function countClient(ClientId $clientId): int
    {
        $selectSql = <<<SQL
    SELECT count(id)
    FROM pim_api_client
    WHERE id = :id
SQL;

        $stmt = $this->dbalConnection->executeQuery(
            $selectSql,
            [
                'id' => $clientId->id()
            ]
        );

        $count = $stmt->fetchColumn();

        return (int) $count;
    }
}
