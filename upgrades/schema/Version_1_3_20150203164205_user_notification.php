<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Class Version_1_3_20150203164205_user_notification
 *
 * @author    Stephane Chapeau <stephane.chapeau@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_1_3_20150203164205_user_notification extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE pim_notification_user_notification (id INT AUTO_INCREMENT NOT NULL, notification INT DEFAULT NULL, user INT DEFAULT NULL, viewed TINYINT(1) NOT NULL, INDEX IDX_342AA855BF5476CA (notification), INDEX IDX_342AA8558D93D649 (user), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pim_notification_notification (id INT AUTO_INCREMENT NOT NULL, route VARCHAR(255) DEFAULT NULL, routeParams LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', message VARCHAR(255) NOT NULL, messageParams LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', created DATETIME NOT NULL, type VARCHAR(20) NOT NULL, context LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pim_notification_user_notification ADD CONSTRAINT FK_342AA855BF5476CA FOREIGN KEY (notification) REFERENCES pim_notification_notification (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pim_notification_user_notification ADD CONSTRAINT FK_342AA8558D93D649 FOREIGN KEY (user) REFERENCES oro_user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema)
    {
        throw new \RuntimeException('No revert is provided for the migrations.');
    }
}
