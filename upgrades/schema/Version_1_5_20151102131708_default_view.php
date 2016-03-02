<?php

namespace Pim\Upgrade\schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add a join table for default grid views
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_1_5_20151102131708_default_view extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE pim_user_default_datagrid_view (user_id INT NOT NULL, view_id INT NOT NULL, INDEX IDX_3CEEC2F2A76ED395 (user_id), INDEX IDX_3CEEC2F231518C7 (view_id), PRIMARY KEY(user_id, view_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pim_user_default_datagrid_view ADD CONSTRAINT FK_3CEEC2F2A76ED395 FOREIGN KEY (user_id) REFERENCES oro_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pim_user_default_datagrid_view ADD CONSTRAINT FK_3CEEC2F231518C7 FOREIGN KEY (view_id) REFERENCES pim_datagrid_view (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
