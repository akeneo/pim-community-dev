<?php

namespace Pim\Upgrade\Schema;

use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version_2_3_20180716135306_update_job_instances extends AbstractMigration implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    private $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Replace "sku" by "identifier" in the filters of all the product model export job instances.
     * See PIM-7517
     *
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $jobInstanceRepository = $this->container->get('pim_enrich.repository.job_instance');
        $entityManager = $this->container->get('doctrine.orm.entity_manager');

        $jobInstances = $jobInstanceRepository->findBy(['jobName' => [
            'xlsx_product_model_export',
            'csv_product_model_export'
        ]]);

        foreach ($jobInstances as $jobInstance) {
            $this->updateJobInstance($jobInstance);
            $entityManager->persist($jobInstance);
        }

        $entityManager->flush();
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
     * @param JobInstance $jobInstance
     */
    private function updateJobInstance(JobInstance $jobInstance)
    {
        $rawParameters = $jobInstance->getRawParameters();

        foreach ($rawParameters['filters']['data'] as $index => $filter) {
            if (isset($filter['field']) && $filter['field'] === 'sku') {
                $filter['field'] = 'identifier';
                $rawParameters['filters']['data'][$index] = $filter;
            }
        }

        $jobInstance->setRawParameters($rawParameters);
    }

    /**
     * Function that does a non altering operation on the DB using SQL to hide the doctrine warning stating that no
     * sql query has been made to the db during the migration process.
     */
    private function disableMigrationWarning()
    {
        $this->addSql('SELECT 1');
    }
}
