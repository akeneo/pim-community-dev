<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20220405000000_remove_group_all_from_apps extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
DELETE FROM oro_user_access_group
WHERE user_id IN (
    SELECT oro_user.id
    FROM oro_user
    JOIN akeneo_connectivity_connection on oro_user.id = akeneo_connectivity_connection.user_id
    WHERE akeneo_connectivity_connection.type = 'app'
)
AND group_id = (
    SELECT oro_access_group.id
    FROM oro_access_group
    WHERE oro_access_group.name = 'All'
    LIMIT 1
)
SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    public function getDescription(): string
    {
        return 'Remove the UserGroup ALL from connected Apps';
    }
}
