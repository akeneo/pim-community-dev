<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class Version_7_0_20221114112755_clean_family_codes_from_export_job_filters extends AbstractMigration implements ContainerAwareInterface
{
    private ?ContainerInterface $container;

    public function setContainer(ContainerInterface $container = null): void
    {
        $this->container = $container;
    }

    public function up(Schema $schema): void
    {
        $familyCodes = $this->fetchFamilyCodes();
        $jobInstances = $this->fetchJobInstances();
        $jobInstancesToClean = $this->filterJobInstanceToClean($jobInstances, $familyCodes);

        $this->skipIf(empty($jobInstancesToClean), 'Export job filters are already cleaned');

        foreach ($jobInstancesToClean as $jobInstance) {
            $cleanedRawParameters = $this->cleanRawParameters($jobInstance['raw_parameters'], $familyCodes);
            $this->updateJobInstanceRawParameters($jobInstance['code'], $cleanedRawParameters);
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function fetchFamilyCodes(): array
    {
        $sql = <<<SQL
SELECT code
FROM pim_catalog_family
SQL;

        $stmt = $this->getConnection()->executeQuery($sql);

        return $stmt->fetchFirstColumn();
    }

    private function fetchJobInstances(): array
    {
        $sql = <<<SQL
SELECT code, raw_parameters
FROM akeneo_batch_job_instance
WHERE type = 'export'
SQL;

        $stmt = $this->getConnection()->executeQuery($sql);
        $rawJobInstances = $stmt->fetchAllAssociative();

        return array_map(static function (array $jobInstance) {
            $jobInstance['raw_parameters'] = unserialize($jobInstance['raw_parameters']);
            return $jobInstance;
        }, $rawJobInstances);
    }

    private function filterJobInstanceToClean(array $jobInstances, array $familyCodes): array
    {
        return array_filter($jobInstances, static function (array $jobInstance) use ($familyCodes) {
            $filters = $jobInstance['raw_parameters']['filters']['data'] ?? [];
            $filters = array_values(array_filter($filters, static fn (array $filter) => 'family' === $filter['field']));

            if (empty($filters)) {
                return false;
            }

            $familyCodesInFilter = $filters[0]['value'];
            $wrongFamilyCodesInFilter = array_diff($familyCodesInFilter, $familyCodes);
            return !empty($wrongFamilyCodesInFilter);
        });
    }

    private function cleanRawParameters(array $rawParameters, array $familyCodes): array
    {
        foreach ($rawParameters['filters']['data'] as $index => $filter) {
            if ('family' === $filter['field']) {
                $cleanedValue = array_uintersect($familyCodes, $filter['value'], 'strcasecmp');
                if (empty($cleanedValue)) {
                    unset($rawParameters['filters']['data'][$index]);
                    continue;
                }
                $rawParameters['filters']['data'][$index]['value'] = $cleanedValue;
            }
        }

        return $rawParameters;
    }

    private function updateJobInstanceRawParameters(string $jobCode, array $rawParameters): void
    {
        $sql = <<<SQL
UPDATE akeneo_batch_job_instance
SET raw_parameters = :raw_parameters
WHERE code = :code
SQL;

        $this->addSql($sql, ['code' => $jobCode, 'raw_parameters' => serialize($rawParameters)]);
    }

    /**
     * @return Connection
     */
    private function getConnection(): object
    {
        return $this->container->get('database_connection');
    }
}
