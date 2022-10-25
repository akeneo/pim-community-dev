<?php

declare(strict_types=1);

namespace Akeneo\Platform\JobAutomation\Test\Integration\Infrastructure\Query;

use Akeneo\Platform\JobAutomation\Domain\Exception\AsymmetricKeyNotFoundException;
use Akeneo\Platform\JobAutomation\Domain\Model\AsymmetricKeys;
use Akeneo\Platform\JobAutomation\Domain\Query\GetAsymmetricKeysQueryInterface;
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
        $this->expectException(AsymmetricKeyNotFoundException::class);
        $this->expectExceptionMessage(sprintf(AsymmetricKeyNotFoundException::MESSAGE, 'an_asymmetric_keys_code'));

        $this->query->execute('an_asymmetric_keys_code');
    }

    public function test_it_gets_asymmetric_keys_from_the_database(): void
    {
        $this->addAsymmetricKeys('an_asymmetric_keys_code', 'the_private_key', 'the_public_key');

        $result = $this->query->execute('an_asymmetric_keys_code');

        $this->assertInstanceOf(AsymmetricKeys::class, $result);
        $this->assertEquals(
            [AsymmetricKeys::PRIVATE_KEY => 'the_private_key', AsymmetricKeys::PUBLIC_KEY => 'the_public_key'],
            $result->normalize()
        );
    }

    private function resetPimConfiguration(): void
    {
        $this->connection->executeQuery('DELETE FROM pim_configuration');
    }

    private function addAsymmetricKeys(string $code, string $privateKey, string $publicKey): void
    {
        $query = <<<SQL
            INSERT INTO pim_configuration (`code`,`values`)
            VALUES (:code, :asymmetricKeys)
            ON DUPLICATE KEY UPDATE `values`= :asymmetricKeys
        SQL;

        $this->connection->executeQuery($query, [
            'code' => $code,
            'asymmetricKeys' => ['private_key' => $privateKey, 'public_key' => $publicKey]
        ], [
            'code' => Types::STRING,
            'asymmetricKeys' => Types::JSON,
        ]);
    }
}
