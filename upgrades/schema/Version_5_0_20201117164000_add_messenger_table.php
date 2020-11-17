<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_5_0_20201117164000_add_messenger_table extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $sql = <<<SQL
CREATE TABLE messenger_messages (
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
                `headers` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
                `queue_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime)',
                `available_at` datetime NOT NULL COMMENT '(DC2Type:datetime)',
                `delivered_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime)',
                PRIMARY KEY (`id`),
                KEY `IDX_75EA56E0FB7336F0` (`queue_name`),
                KEY `IDX_75EA56E0E3BD61CE` (`available_at`),
                KEY `IDX_75EA56E016BA31DB` (`delivered_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
SQL;
        $this->addSql($sql);
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
