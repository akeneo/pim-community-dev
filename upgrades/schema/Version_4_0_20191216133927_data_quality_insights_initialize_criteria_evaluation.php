<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class Version_4_0_20191216133927_data_quality_insights_initialize_criteria_evaluation extends AbstractMigration implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    private $container;

    public function getDescription()
    {
        return 'Schedule the evaluation of all the criterion of all the products to be able to compute the data quality insights grades.';
    }

    public function up(Schema $schema) : void
    {
        $this->disableMigrationWarning();

        $this->initializeDictionary();
        $initializeCriteriaEvaluation = $this->container->get('Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Install\InitializeCriteriaEvaluation');
        $initializeCriteriaEvaluation->initialize();

    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }

    /**
     * Function that does a non altering operation on the DB using SQL to hide the doctrine warning stating that no
     * sql query has been made to the db during the migration process.
     */
    private function disableMigrationWarning()
    {
        $this->addSql('SELECT * FROM oro_user LIMIT 1');
    }

    /**
     * @inheritDoc
     */
    public function setContainer(ContainerInterface $container = null)
    {
         $this->container = $container;
    }

    private function initializeDictionary(): void
    {
        $dictionaryGenerator = $this->container->get('Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Aspell\AspellDictionaryGenerator');
        $dictionarySource = $this->container->get('Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Aspell\ProductValueInDatabaseDictionarySource');

        $dictionaryGenerator->generate($dictionarySource);
    }
}
