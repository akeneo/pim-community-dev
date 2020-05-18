<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class Version_5_0_20200421154433_franklin_insights_rename_jobs_labels extends AbstractMigration implements ContainerAwareInterface
{
    /** * @var ContainerInterface */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('UPDATE akeneo_batch_job_instance SET label = "Mass unsubscribe products" WHERE code = "franklin_insights_unsubscribe_products"');
        $this->addSql('UPDATE akeneo_batch_job_instance SET label = "Mass subscribe products" WHERE code = "franklin_insights_subscribe_products"');
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
