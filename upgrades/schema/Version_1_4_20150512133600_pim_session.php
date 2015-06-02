<?php
namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
/**
 * Migration 1.3 => 1.4
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_1_3_20150503133600_pim_session extends AbstractMigration implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    protected $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $sessionTableSql = 'CREATE TABLE pim_session (
+                sess_id VARBINARY(128) NOT NULL PRIMARY KEY,
+                sess_data BLOB NOT NULL,
+                sess_time INTEGER UNSIGNED NOT NULL,
+                sess_lifetime MEDIUMINT NOT NULL
+            ) COLLATE utf8_bin, ENGINE = InnoDB';

        $this->addSql($sessionTableSql);
    }
    public function down(Schema $schema)
    {
        throw new \RuntimeException('No revert is provided for the migrations.');
    }
}
