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
final class Version_5_0_20200630044018_create_communication_channel_viewed_announcements_table extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS akeneo_communication_channel_viewed_announcements(
    announcement_id VARCHAR(100) NOT NULL,
    user_id INT NOT NULL,
    PRIMARY KEY (announcement_id, user_id),
    CONSTRAINT FK_COMMUNICATION_CHANNEL_VIEWED_ANNOUNCEMENTS_user_id FOREIGN KEY (user_id) REFERENCES oro_user (id) ON DELETE CASCADE
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC
SQL;

        $this->addSql($sql);

    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
