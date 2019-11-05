<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Apps\Infrastructure\Install\Query\CreateAppsTableQuery;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * This migration will create the apps table
 *
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class Version_4_0_20191014111427_create_apps_table extends AbstractMigration
{

    public function up(Schema $schema) : void
    {
        $this->addSql(CreateAppsTableQuery::QUERY);
    }

    public function down(Schema $schema) : void
    {
        $dropTableQuery = <<<SQL
DROP TABLE akeneo_app
SQL;
        $this->addSql($dropTableQuery);
    }
}
