<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionIsActiveQuery;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class Version_4_0_20200129102033_franklin_insights_fill_pending_items extends AbstractMigration implements ContainerAwareInterface
{
    /** * @var ContainerInterface */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema) : void
    {
       $connectionStatusHandler = $this->container->get('akeneo.pim.automation.franklin_insights.application.configuration.query.get_connection_is_active_handler');

        if (true === $connectionStatusHandler->handle(new GetConnectionIsActiveQuery())) {
            $repository = $this->container->get('akeneo.pim.automation.franklin_insights.repository.quality_highlights_pending_items');
            $repository->fillWithAllAttributes();
            $repository->fillWithAllFamilies();
            $repository->fillWithAllProducts();
        }
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
