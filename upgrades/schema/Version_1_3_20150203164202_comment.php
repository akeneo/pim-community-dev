<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Class Version_1_3_20150203164202_comment
 *
 * @author    Stephane Chapeau <stephane.chapeau@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_1_3_20150203164202_comment extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE pim_comment_comment (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, author_id INT DEFAULT NULL, resource_name VARCHAR(255) NOT NULL, resource_id VARCHAR(24) NOT NULL, body LONGTEXT NOT NULL, created_at DATETIME NOT NULL, replied_at DATETIME NOT NULL, INDEX IDX_2A32D03D727ACA70 (parent_id), INDEX IDX_2A32D03DF675F31B (author_id), INDEX resource_name_resource_id_idx (resource_name, resource_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pim_comment_comment ADD CONSTRAINT FK_2A32D03D727ACA70 FOREIGN KEY (parent_id) REFERENCES pim_comment_comment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pim_comment_comment ADD CONSTRAINT FK_2A32D03DF675F31B FOREIGN KEY (author_id) REFERENCES oro_user (id)');
    }

    public function down(Schema $schema)
    {
        throw new \RuntimeException('No revert is provided for the migrations.');
    }
}
