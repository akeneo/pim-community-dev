<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Migrate the job parameters of product model exports (csv and xlsx)
 */
class Version_2_2_20180321135455_product_model_export_job_parameters extends AbstractMigration
    implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $jobInstanceRepo = $this->container->get('pim_enrich.repository.job_instance');

        $csvExportjobInstances = $jobInstanceRepo->findBy(['jobName' => 'csv_product_model_export']);
        $this->migrateJobParameters($csvExportjobInstances);

        $xlsxExportjobInstances = $jobInstanceRepo->findBy(['jobName' => 'xlsx_product_model_export']);
        $this->migrateJobParameters($xlsxExportjobInstances);

        $this->disableMigrationWarning();
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

    /**
     * @param array $csvExportjobInstances
     */
    private function migrateJobParameters(array $csvExportjobInstances): void
    {
        $channelRepo = $this->container->get('pim_catalog.repository.channel');
        $entityManager = $this->container->get('doctrine.orm.entity_manager');
        $validator = $this->container->get('validator');
        foreach ($csvExportjobInstances as $jobInstance) {
            $parameters = $jobInstance->getRawParameters();
            $parameters['filters'] = [
                'data'      => [],
                'structure' => [
                    'locales' => []
                ],
            ];

            $jobInstance->setRawParameters($parameters);
            $errors = $validator->validate($jobInstance);

            if (count($errors) === 0) {
                $entityManager->persist($jobInstance);
                $entityManager->flush();
            }
        }
    }

    /**
     * Function that does a non altering operation on the DB using SQL to hide the doctrine warning stating that no
     * sql query has been made to the db during the migration process.
     */
    private function disableMigrationWarning()
    {
        $this->addSql('SELECT * FROM oro_user LIMIT 1');
    }
}
