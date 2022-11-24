<?php

declare(strict_types=1);

namespace Akeneo\Platform\JobAutomation\Test\Integration\Infrastructure\Query;

use Akeneo\Platform\JobAutomation\Domain\Exception\AsymmetricKeysNotFoundException;
use Akeneo\Platform\JobAutomation\Domain\Model\AsymmetricKeys;
use Akeneo\Platform\JobAutomation\Domain\Query\GetAsymmetricKeysQueryInterface;
use Akeneo\Platform\JobAutomation\Infrastructure\Query\SaveAsymmetricKeysQuery;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

class GetAsymmetricKeysQueryTest extends TestCase
{
    private GetAsymmetricKeysQueryInterface $query;
    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->query = $this->get(GetAsymmetricKeysQueryInterface::class);
        $this->connection = $this->get('database_connection');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_throws_an_exception_when_there_is_no_asymmetric_keys_into_the_database(): void
    {
        $this->resetPimConfiguration();
        $this->expectException(AsymmetricKeysNotFoundException::class);
        $this->expectExceptionMessage(AsymmetricKeysNotFoundException::MESSAGE);

        $this->query->execute();
    }

    public function test_it_gets_asymmetric_keys_from_the_database(): void
    {
        $this->addAsymmetricKeys('the_public_key', 'the_private_key');

        $result = $this->query->execute();

        $this->assertInstanceOf(AsymmetricKeys::class, $result);
        $this->assertEquals(
            [AsymmetricKeys::PUBLIC_KEY => 'the_public_key', AsymmetricKeys::PRIVATE_KEY => 'the_private_key'],
            $result->normalize()
        );
    }

    private function resetPimConfiguration(): void
    {
        $this->connection->executeQuery('DELETE FROM pim_configuration');
    }

    private function addAsymmetricKeys(string $publicKey, string $privateKey): void
    {
        $query = <<<SQL
            INSERT INTO pim_configuration (`code`,`values`)
            VALUES (:code, :asymmetricKeys)
            ON DUPLICATE KEY UPDATE `values`= :asymmetricKeys
        SQL;

        $this->connection->executeQuery($query, [
            'code' => SaveAsymmetricKeysQuery::OPTION_CODE,
            'asymmetricKeys' => ['public_key' => $publicKey, 'private_key' => $privateKey]
        ], [
            'code' => Types::STRING,
            'asymmetricKeys' => Types::JSON,
        ]);
    }
}
