<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_6_0_20210727080505_add_client_id_and_user_id extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE pim_api_auth_code ADD client_id INT DEFAULT NULL, ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pim_api_auth_code ADD CONSTRAINT FK_AD5DC7C619EB6921 FOREIGN KEY (client_id) REFERENCES pim_api_client (id)');
        $this->addSql('ALTER TABLE pim_api_auth_code ADD CONSTRAINT FK_AD5DC7C6A76ED395 FOREIGN KEY (user_id) REFERENCES oro_user (id)');
        $this->addSql('CREATE INDEX IDX_CLIENT_ID ON pim_api_auth_code (client_id)');
        $this->addSql('CREATE INDEX IDX_USER_ID ON pim_api_auth_code (user_id)');
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
