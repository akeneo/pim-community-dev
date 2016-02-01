<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Class Version_1_4_20150922153314_drop_oro_tables
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_1_4_20150922153314_drop_oro_tables extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE oro_access_group DROP FOREIGN KEY FK_FEF9EDB759294170');
        $this->addSql('ALTER TABLE oro_access_role DROP FOREIGN KEY FK_673F65E759294170');
        $this->addSql('ALTER TABLE oro_business_unit DROP FOREIGN KEY FK_C033B2D559294170');
        $this->addSql('ALTER TABLE oro_user DROP FOREIGN KEY FK_F82840BC59294170');
        $this->addSql('ALTER TABLE oro_user_business_unit DROP FOREIGN KEY FK_B190CE8FA58ECB40');
        $this->addSql('ALTER TABLE oro_email_body DROP FOREIGN KEY FK_C7CE120DA832C1C9');
        $this->addSql('ALTER TABLE oro_email_imap DROP FOREIGN KEY FK_17E00D83A832C1C9');
        $this->addSql('ALTER TABLE oro_email_recipient DROP FOREIGN KEY FK_7DAF9656A832C1C9');
        $this->addSql('ALTER TABLE oro_email DROP FOREIGN KEY FK_2A30C171D445573A');
        $this->addSql('ALTER TABLE oro_email_recipient DROP FOREIGN KEY FK_7DAF965659045DAA');
        $this->addSql('ALTER TABLE oro_email_attachment_content DROP FOREIGN KEY FK_18704959464E68B');
        $this->addSql('ALTER TABLE oro_email_attachment DROP FOREIGN KEY FK_F4427F239B621D84');
        $this->addSql('ALTER TABLE oro_email DROP FOREIGN KEY FK_2A30C171162CB942');
        $this->addSql('ALTER TABLE oro_email_folder_imap DROP FOREIGN KEY FK_EC4034F9162CB942');
        $this->addSql('ALTER TABLE oro_email_folder DROP FOREIGN KEY FK_EB940F1C56A273CC');
        $this->addSql('ALTER TABLE oro_user DROP FOREIGN KEY FK_F82840BC678BF607');
        $this->addSql('ALTER TABLE oro_email_template_translation DROP FOREIGN KEY FK_F42DCDB8232D562B');
        $this->addSql('ALTER TABLE oro_entity_config_field DROP FOREIGN KEY FK_63EC23F781257D5D');
        $this->addSql('ALTER TABLE oro_entity_config_value DROP FOREIGN KEY FK_256E3E9B81257D5D');
        $this->addSql('ALTER TABLE oro_entity_config_optionset DROP FOREIGN KEY FK_CDC152C4443707B0');
        $this->addSql('ALTER TABLE oro_entity_config_optionset_relation DROP FOREIGN KEY FK_797D3D83443707B0');
        $this->addSql('ALTER TABLE oro_entity_config_value DROP FOREIGN KEY FK_256E3E9B443707B0');
        $this->addSql('ALTER TABLE oro_entity_config_log_diff DROP FOREIGN KEY FK_D1F6D75AEA675D86');
        $this->addSql('ALTER TABLE oro_entity_config_optionset_relation DROP FOREIGN KEY FK_797D3D83A7C41D6F');
        $this->addSql('ALTER TABLE oro_business_unit DROP FOREIGN KEY FK_C033B2D532C8A3DE');
        $this->addSql('ALTER TABLE oro_user DROP FOREIGN KEY FK_F82840BC6BF700BD');
        $this->addSql('DROP TABLE oro_audit');
        $this->addSql('DROP TABLE oro_business_unit');
        $this->addSql('DROP TABLE oro_email');
        $this->addSql('DROP TABLE oro_email_address');
        $this->addSql('DROP TABLE oro_email_attachment');
        $this->addSql('DROP TABLE oro_email_attachment_content');
        $this->addSql('DROP TABLE oro_email_body');
        $this->addSql('DROP TABLE oro_email_folder');
        $this->addSql('DROP TABLE oro_email_folder_imap');
        $this->addSql('DROP TABLE oro_email_imap');
        $this->addSql('DROP TABLE oro_email_origin');
        $this->addSql('DROP TABLE oro_email_recipient');
        $this->addSql('DROP TABLE oro_email_template');
        $this->addSql('DROP TABLE oro_email_template_translation');
        $this->addSql('DROP TABLE oro_entity_config');
        $this->addSql('DROP TABLE oro_entity_config_field');
        $this->addSql('DROP TABLE oro_entity_config_log');
        $this->addSql('DROP TABLE oro_entity_config_log_diff');
        $this->addSql('DROP TABLE oro_entity_config_optionset');
        $this->addSql('DROP TABLE oro_entity_config_optionset_relation');
        $this->addSql('DROP TABLE oro_entity_config_value');
        $this->addSql('DROP TABLE oro_organization');
        $this->addSql('DROP TABLE oro_session');
        $this->addSql('DROP TABLE oro_user_business_unit');
        $this->addSql('DROP TABLE oro_user_email');
        $this->addSql('DROP TABLE oro_user_status');
        $this->addSql('DROP TABLE oro_windows_state');
        $this->addSql('DROP INDEX IDX_FEF9EDB759294170 ON oro_access_group');
        $this->addSql('ALTER TABLE oro_access_group DROP business_unit_owner_id');
        $this->addSql('DROP INDEX IDX_673F65E759294170 ON oro_access_role');
        $this->addSql('ALTER TABLE oro_access_role DROP business_unit_owner_id');
        $this->addSql('DROP INDEX UNIQ_F82840BC6BF700BD ON oro_user');
        $this->addSql('DROP INDEX UNIQ_F82840BC678BF607 ON oro_user');
        $this->addSql('DROP INDEX IDX_F82840BC59294170 ON oro_user');
        $this->addSql('ALTER TABLE oro_user DROP business_unit_owner_id, DROP imap_configuration_id, DROP status_id');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
