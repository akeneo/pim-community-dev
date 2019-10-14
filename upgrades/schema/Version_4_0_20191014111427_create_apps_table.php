<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Apps\Infrastructure\Install\Query\CreateAppsTableQuery;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This migration will create the apps table
 *
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class Version_4_0_20191014111427_create_apps_table
    extends AbstractMigration
    implements ContainerAwareInterface
{
    private $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema) : void
    {
        $dbalConnection = $this->container->get('database_connection');
        $dbalConnection->exec(CreateAppsTableQuery::QUERY);
    }

    public function down(Schema $schema) : void
    {
        $dropTableQuery = <<<SQL
DROP TABLE akeneo_app
SQL;

        $dbalConnection = $this->container->get('database_connection');
        $dbalConnection->exec($dropTableQuery);
    }
}
