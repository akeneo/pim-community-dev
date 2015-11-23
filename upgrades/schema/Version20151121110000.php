<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Pim\Upgrade\SchemaHelper;
use Pim\Upgrade\UpgradeHelper;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * MONGO migration only
 *
 * Add index on mongo Version document, column loggetAt
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version20151121110000 extends AbstractMigration implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    protected $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema)
    {
    }

    public function down(Schema $schema)
    {
        throw new \RuntimeException('No revert is provided for the migrations.');
    }

    public function postUp(Schema $schema)
    {
        $upgradeHelper = new UpgradeHelper($this->container);
        if ($upgradeHelper->areProductsStoredInMongo()) {
            $database = $upgradeHelper->getMongoInstance();
            $tableHelper = new SchemaHelper($this->container);

            echo "Add index to Version document on column loggetAt...\n";
            $versionCollection = new \MongoCollection($database, $tableHelper->getTableOrCollection('version'));
            $versionCollection->createIndex(['loggedAt' => -1], ['background' => true]);
            echo "Done.";
        }
    }
}
