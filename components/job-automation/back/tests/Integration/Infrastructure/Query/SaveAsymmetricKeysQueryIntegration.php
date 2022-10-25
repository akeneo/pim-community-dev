<?php
declare(strict_types=1);

namespace Akeneo\Platform\JobAutomation\Test\Integration\Infrastructure\Query;

use Akeneo\Platform\JobAutomation\Domain\Query\SaveAsymmetricKeysQueryInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

class SaveAsymmetricKeysQueryIntegration extends TestCase
{
    private SaveAsymmetricKeysQueryInterface $query;
    private Connection $connection;
    private FakeClock $clock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->query = $this->get(SaveAsymmetricKeysQueryInterface::class);
        $this->connection = $this->get('database_connection');
        $this->clock = $this->get(SystemClock::class);
        $this->clock->setNow(new \DateTimeImmutable('2021-03-02T04:30:11'));
    }

    public function test_it_saves_asymmetric_keys_into_the_database(): void
    {
        $this->resetPimConfiguration();

        $selectQuery = 'SELECT * FROM pim_configuration WHERE code=:code';

        $result = $this->connection->executeQuery(
            $selectQuery,
            [
                'code' => SaveAsymmetricKeysQuery::OPTION_CODE,
            ],
            [
                'code' => Types::STRING,
            ]
        )->fetchAssociative();

        $this->assertFalse($result);

        $keys = AsymmetricKeys::create('the_public_key', 'the_private_key');

        $this->query->execute($keys);

        $result = $this->connection->executeQuery(
            $selectQuery,
            [
                'code' => SaveAsymmetricKeysQuery::OPTION_CODE,
            ],
            [
                'code' => Types::STRING,
            ]
        )->fetchAssociative();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('code', $result);
        $this->assertArrayHasKey('values', $result);
        $this->assertEquals(SaveAsymmetricKeysQuery::OPTION_CODE, $result['code']);
        $this->assertEquals([
            AsymmetricKeys::PUBLIC_KEY => 'the_public_key',
            AsymmetricKeys::PRIVATE_KEY => 'the_private_key',
            'updated_at' => $this->clock->now()->format(\DateTimeInterface::ATOM),
        ], \json_decode($result['values'], true));
    }

    public function test_it_overrides_asymmetric_keys_into_the_database(): void
    {
        $selectQuery = 'SELECT * FROM pim_configuration WHERE code=:code';

        $this->query->execute(AsymmetricKeys::create('the_public_key', 'the_private_key'));
        $this->query->execute(AsymmetricKeys::create('the_new_public_key', 'the_new_private_key'));

        $result = $this->connection->executeQuery(
            $selectQuery,
            [
                'code' => SaveAsymmetricKeysQuery::OPTION_CODE,
            ],
            [
                'code' => Types::STRING,
            ]
        )->fetchAssociative();

        $this->assertEquals([
            AsymmetricKeys::PUBLIC_KEY => 'the_new_public_key',
            AsymmetricKeys::PRIVATE_KEY => 'the_new_private_key',
            'updated_at' => $this->clock->now()->format(\DateTimeInterface::ATOM),
        ], \json_decode($result['values'], true));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function resetPimConfiguration(): void
    {
        $this->connection->executeQuery('DELETE FROM pim_configuration');
    }
}
