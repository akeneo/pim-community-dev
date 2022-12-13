<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_7_0_20221212160000_add_database_install_time extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initialize the database install time if not present';
    }

    public function up(Schema $schema): void
    {
        $this->skipIf($this->installDataExists(), 'Install data already exists');

        $userTableTime = $this->getUserTableDate();

        $installData = [
            'database_installed_at' => $userTableTime->format('c'),
        ];

        $this->addSql(
            'INSERT INTO pim_configuration (`code`, `values`) VALUES (?, ?)',
            ['install_data', \json_encode($installData)]
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function installDataExists(): bool
    {
        $result = $this->connection->executeQuery(
            'SELECT `values` FROM pim_configuration WHERE code = "install_data"'
        );
        $installData = $result->fetchOne();

        if (!$installData) {
            return false;
        }

        $decodedData = \json_decode($installData, true);

        if (!array_key_exists('database_installed_at', $decodedData)) {
            return false;
        }

        return true;
    }

    private function getUserTableDate(): \DateTimeImmutable
    {
        $sql = 'SELECT create_time FROM INFORMATION_SCHEMA.TABLES
                WHERE table_schema = :database_name
                AND table_name = :install_table_name';

        $result = $this->connection->executeQuery(
            $sql,
            [
                'database_name' => $this->connection->getDatabase(),
                'install_table_name' => 'oro_user',
            ]
        );

        return new \DateTimeImmutable($result->fetchOne());
    }
}
