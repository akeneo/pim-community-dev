<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version_6_0_20211108160902_fix_oauth_code_fk extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $table = $schema->getTable('pim_api_auth_code');

        if ($table->hasForeignKey('FK_AD5DC7C619EB6921')) {
            $this->addSql('ALTER TABLE pim_api_auth_code DROP FOREIGN KEY FK_AD5DC7C619EB6921');
        }
        $this->addSql('ALTER TABLE pim_api_auth_code ADD CONSTRAINT FK_AD5DC7C619EB6921 FOREIGN KEY (client_id) REFERENCES pim_api_client (id) ON DELETE CASCADE');

        if ($table->hasForeignKey('FK_AD5DC7C6A76ED395')) {
            $this->addSql('ALTER TABLE pim_api_auth_code DROP FOREIGN KEY FK_AD5DC7C6A76ED395');
        }
        $this->addSql('ALTER TABLE pim_api_auth_code ADD CONSTRAINT FK_AD5DC7C6A76ED395 FOREIGN KEY (user_id) REFERENCES oro_user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
