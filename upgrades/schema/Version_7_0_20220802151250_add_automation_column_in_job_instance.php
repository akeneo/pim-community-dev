<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class Version_7_0_20220802151250_add_automation_column_in_job_instance extends AbstractMigration implements ContainerAwareInterface
{
    private ?ContainerInterface $container;

    public function setContainer(ContainerInterface $container = null): void
    {
        $this->container = $container;
    }

    public function up(Schema $schema): void
    {
        if (!$this->isColumnAlreadyCreated('automation')) {
            $this->addSql(<<<SQL
                ALTER TABLE akeneo_batch_job_instance 
                ADD COLUMN automation JSON DEFAULT NULL AFTER raw_parameters;
            SQL);
        }

        if (!$this->isColumnAlreadyCreated('scheduled')) {
            $this->addSql(<<<SQL
                ALTER TABLE akeneo_batch_job_instance 
                ADD COLUMN scheduled BOOL DEFAULT FALSE AFTER raw_parameters;
            SQL);
        }
    }

    private function isColumnAlreadyCreated(string $columnName): bool
    {
        $sql = <<<SQL
            SHOW COLUMNS FROM akeneo_batch_job_instance LIKE :columnName;
        SQL;
        $connection = $this->container->get('database_connection');
        $result = $connection->executeQuery($sql, ['columnName' => $columnName])->fetch();
        if (!empty($result)) {
            return true;
        }
        return false;
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
