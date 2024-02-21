<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class Version_7_0_20221121160755_clean_family_codes_from_export_job_filters extends AbstractMigration implements ContainerAwareInterface
{
    private ?ContainerInterface $container;

    public function setContainer(ContainerInterface $container = null): void
    {
        $this->container = $container;
    }

    public function up(Schema $schema): void
    {
        $jobInstances = $this->fetchJobInstances();
        $jobInstancesToClean = $this->filterJobInstanceToClean($jobInstances);

        $this->skipIf(empty($jobInstancesToClean), 'Export job filters are already cleaned');

        foreach ($jobInstancesToClean as $jobInstance) {
            $cleanedRawParameters = $this->cleanRawParameters($jobInstance['raw_parameters']);
            $this->updateJobInstanceRawParameters($jobInstance['code'], $cleanedRawParameters);
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
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

    private function filterJobInstanceToClean(array $jobInstances): array
    {
        return array_filter($jobInstances, static function (array $jobInstance) {
            $filters = $jobInstance['raw_parameters']['filters']['data'] ?? [];
            $familyFilters = array_filter($filters, static fn (array $filter) => 'family' === $filter['field']);
            if (empty($familyFilters)) {
                return false;
            }

            $familyCodesInFilter = current($familyFilters)['value'];

            return in_array('[object Object]', $familyCodesInFilter) || !array_is_list($familyCodesInFilter);
        });
    }

    private function cleanRawParameters(array $rawParameters): array
    {
        foreach ($rawParameters['filters']['data'] as $index => $filter) {
            if ('family' !== $filter['field']) {
                continue;
            }

            $familyFilterValue = array_filter(
                $filter['value'],
                static fn (string $familyCode) => '[object Object]' !== $familyCode
            );

            $rawParameters['filters']['data'][$index]['value'] = array_values($familyFilterValue);
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
