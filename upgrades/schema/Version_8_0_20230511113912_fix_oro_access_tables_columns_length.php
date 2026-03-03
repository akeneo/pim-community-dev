<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * Fix label and role column length to 255 in oro_access_role table and name column length to 255 in oro_access_group table
 */
final class Version_8_0_20230511113912_fix_oro_access_tables_columns_length extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix label and role column length to 255 in oro_access_role table and name column length to 255 in oro_access_group table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
            ALTER TABLE oro_access_role 
                MODIFY label VARCHAR(255) NOT NULL, 
                MODIFY role VARCHAR(255) NOT NULL
            ;
            SQL
        );

        $this->addSql(<<<SQL
            ALTER TABLE akeneo_connectivity_connected_app 
                DROP CONSTRAINT FK_CONNECTIVITY_CONNECTED_APP_user_group_name
            ;
            SQL
        );
        $this->addSql(<<<SQL
            ALTER TABLE oro_access_group 
                MODIFY name VARCHAR(255) NOT NULL
            ;
            SQL
        );
        $this->addSql(<<<SQL
            ALTER TABLE akeneo_connectivity_connected_app 
                ADD CONSTRAINT FK_CONNECTIVITY_CONNECTED_APP_user_group_name FOREIGN KEY (user_group_name) REFERENCES oro_access_group (name);
            SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
