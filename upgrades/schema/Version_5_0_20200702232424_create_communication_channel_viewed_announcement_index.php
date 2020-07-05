<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @author    Christophe Chausseray <chausseray.christophe@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_5_0_20200702232424_create_communication_channel_viewed_announcement_index extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $sql = <<<SQL
            ALTER TABLE akeneo_communication_channel_viewed_announcements
            ADD UNIQUE INDEX IDX_VIEWED_ANNOUNCEMENTS_user_id_announcement_id (user_id, announcement_id);
        SQL;

        $this->addSql($sql);
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
