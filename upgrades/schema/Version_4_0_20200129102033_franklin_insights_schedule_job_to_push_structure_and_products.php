<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionIsActiveQuery;
use Akeneo\Pim\Automation\FranklinInsights\Application\QualityHighlights\PushStructureAndProductsToFranklin;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\BatchSize;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobLauncher\SchedulePushStructureAndProductsToFranklin;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class Version_4_0_20200129102033_franklin_insights_schedule_job_to_push_structure_and_products extends AbstractMigration implements ContainerAwareInterface
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
            $this->container->get(SchedulePushStructureAndProductsToFranklin::class)->schedule(
                new BatchSize(PushStructureAndProductsToFranklin::DEFAULT_ATTRIBUTES_BATCH_SIZE),
                new BatchSize(PushStructureAndProductsToFranklin::DEFAULT_FAMILIES_BATCH_SIZE),
                new BatchSize(PushStructureAndProductsToFranklin::DEFAULT_PRODUCTS_BATCH_SIZE)
            );
        }
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
