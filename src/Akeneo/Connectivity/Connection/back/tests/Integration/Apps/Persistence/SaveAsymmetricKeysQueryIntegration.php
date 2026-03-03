<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AsymmetricKeys;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\SaveAsymmetricKeysQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Service\Clock\FakeClock;
use Akeneo\Connectivity\Connection\Infrastructure\Service\Clock\SystemClock;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SaveAsymmetricKeysQueryIntegration extends TestCase
{
    private SaveAsymmetricKeysQuery $query;
    private Connection $connection;
    private FakeClock $clock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->query = $this->get(SaveAsymmetricKeysQuery::class);
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
        ], \json_decode($result['values'], true, 512, JSON_THROW_ON_ERROR));
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
        ], \json_decode($result['values'], true, 512, JSON_THROW_ON_ERROR));
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
