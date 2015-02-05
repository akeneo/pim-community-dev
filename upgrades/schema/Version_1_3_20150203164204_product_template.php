<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Pim\Upgrade\SchemaHelper;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Version_1_3_20150203164204_product_template
 *
 * @author    Stephane Chapeau <stephane.chapeau@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_1_3_20150203164204_product_template extends AbstractMigration implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $tableHelper = new SchemaHelper($this->container);
        $groupTable = $tableHelper->getTableOrCollection('group');

        $this->addSql('CREATE TABLE pim_catalog_product_template (id INT AUTO_INCREMENT NOT NULL, valuesData LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql(sprintf('ALTER TABLE %s ADD product_template_id INT DEFAULT NULL', $groupTable));
        $this->addSql(sprintf('ALTER TABLE %s ADD CONSTRAINT FK_5D6997EDA9F591A7 FOREIGN KEY (product_template_id) REFERENCES pim_catalog_product_template (id) ON DELETE SET NULL', $groupTable));
        $this->addSql(sprintf('CREATE UNIQUE INDEX UNIQ_5D6997EDA9F591A7 ON %s (product_template_id)', $groupTable));
    }

    public function down(Schema $schema)
    {
        throw new \RuntimeException('No revert is provided for the migrations.');
    }
}
