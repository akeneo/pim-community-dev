<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_7_0_20220818143128_remove_zdd_setidentifiernullable_from_pim_one_time_task_table extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DELETE FROM pim_one_time_task where code = "zdd_SetProductIdentifierNullable"');
    }

    public function down(Schema $schema): void
    {
        $sql = <<<SQL
INSERT INTO `pim_one_time_task` (`code`, `status`, `start_time`, `end_time`, `values`)
VALUES
	('zdd_SetProductIdentifierNullable', 'finished', '2022-08-17 17:19:48', NULL, '{}');
SQL;

        $this->addSql($sql);
    }
}
