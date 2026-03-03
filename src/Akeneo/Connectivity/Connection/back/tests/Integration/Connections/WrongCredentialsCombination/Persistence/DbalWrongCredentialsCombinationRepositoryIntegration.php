<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Connections\WrongCredentialsCombination\Persistence;

use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Model\Read;
use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Model\Write;
use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Persistence\Repository\WrongCredentialsCombinationRepositoryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Connections\WrongCredentialsCombination\Persistence\DbalWrongCredentialsCombinationRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection as DbalConnection;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DbalWrongCredentialsCombinationRepositoryIntegration extends TestCase
{
    private WrongCredentialsCombinationRepositoryInterface $repository;
    private DbalConnection $dbalConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbalConnection = $this->get('database_connection');
        $this->repository = $this->get(DbalWrongCredentialsCombinationRepository::class);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_saves_a_wrong_credentials_combination(): void
    {
        $this->repository->create(new Write\WrongCredentialsCombination('erp', 'admin'));

        $expected = [
            ['connection_code' => 'erp', 'username' => 'admin']
        ];

        $sql = <<<SQL
        SELECT connection_code, username FROM akeneo_connectivity_connection_wrong_credentials_combination;
        SQL;
        $result = $this->dbalConnection->executeQuery($sql)->fetchAllAssociative();

        Assert::assertSame($expected, $result);
    }

    public function test_it_saves_only_one_entry_for_each_credentials_combination(): void
    {
        $this->repository->create(new Write\WrongCredentialsCombination('ecommerce', 'admin'));
        $this->repository->create(new Write\WrongCredentialsCombination('erp', 'admin'));
        $this->repository->create(new Write\WrongCredentialsCombination('erp', 'julia'));
        $this->repository->create(new Write\WrongCredentialsCombination('erp', 'julia'));

        $expected = [
            ['connection_code' => 'ecommerce', 'username' => 'admin'],
            ['connection_code' => 'erp', 'username' => 'admin'],
            ['connection_code' => 'erp', 'username' => 'julia'],
        ];

        $sql = <<<SQL
        SELECT connection_code, username FROM akeneo_connectivity_connection_wrong_credentials_combination;
        SQL;
        $result = $this->dbalConnection->executeQuery($sql)->fetchAllAssociative();

        Assert::assertSame($expected, $result);
    }

    public function test_it_keeps_only_the_last_wrong_credentials_combination(): void
    {
        $sql = <<<SQL
        SELECT authentication_date FROM akeneo_connectivity_connection_wrong_credentials_combination;
        SQL;

        $this->repository->create(new Write\WrongCredentialsCombination('erp', 'admin'));
        $previous = $this->dbalConnection->executeQuery($sql)->fetchAllAssociative();

        \sleep(1);

        $this->repository->create(new Write\WrongCredentialsCombination('erp', 'admin'));
        $current = $this->dbalConnection->executeQuery($sql)->fetchAllAssociative();

        Assert::assertNotSame($previous, $current);
    }

    public function test_it_finds_all_the_wrong_credentials_conbination(): void
    {
        $sql = <<<SQL
        INSERT INTO akeneo_connectivity_connection_wrong_credentials_combination (connection_code, username, authentication_date) VALUES
        ('ecommerce', 'julia', '2019-12-31 00:00:00'),
        ('ecommerce', 'admin', '2020-01-01 00:00:00'),
        ('erp', 'admin', '2020-01-01 00:00:00'),
        ('erp', 'julia', '2020-01-02 00:00:00');
        SQL;
        $this->dbalConnection->executeQuery($sql);

        $expected = new Read\WrongCredentialsCombinations([
            [
                'connection_code' => 'ecommerce',
                'users' => [
                    'admin' => '2020-01-01T00:00:00+00:00',
                ]
            ],
            [
                'connection_code' => 'erp',
                'users' => [
                    'admin' => '2020-01-01T00:00:00+00:00',
                    'julia' => '2020-01-02T00:00:00+00:00'
                ]
            ]
        ]);

        $result = $this->repository->findAll(new \DateTimeImmutable('2020-01-01T00:00:00+00:00'));

        Assert::assertEquals($expected, $result);
    }
}
