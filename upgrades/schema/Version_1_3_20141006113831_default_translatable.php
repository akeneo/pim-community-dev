<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Pim\Upgrade\SchemaHelper;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Migration 1.2 => 1.3
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_1_3_20141006113831_default_translatable extends AbstractMigration implements ContainerAwareInterface
{
    protected $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $tableHelper = new SchemaHelper($this->container);
        $this->addSql(
            sprintf('ALTER TABLE %s DROP is_default, DROP is_translatable', $tableHelper->getTableOrCollection('attribute_option'))
        );
    }

    public function down(Schema $schema)
    {
        throw new \RuntimeException('No revert is provided for the migrations.');
    }
}
