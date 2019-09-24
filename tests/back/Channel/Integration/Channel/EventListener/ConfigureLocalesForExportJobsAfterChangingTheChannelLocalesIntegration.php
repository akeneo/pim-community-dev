<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Channel\Integration\Channel\EventListener;

use Akeneo\Channel\Component\Event\ChannelLocalesHaveBeenUpdated;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\Batch\Model\JobInstance;

class ConfigureLocalesForExportJobsAfterChangingTheChannelLocalesIntegration extends TestCase
{
    public function testRemoveOneLocaleFromAChannel()
    {
        $jobInstance = $this->createJobInstanceWithLocaleFilter('job1', 'tablet', ['en_US', 'fr_FR', 'de_DE']);
        $rawParameters = $jobInstance->getRawParameters();
        $locales = $rawParameters['filters']['structure']['locales'];
        sort($locales);
        $this->assertSame(['de_DE', 'en_US', 'fr_FR'], $locales);

        $this
            ->getFromTestContainer('pim_catalog.event_subscriber.remove_locale_filter_in_job_instances')
            ->onChannelLocalesHaveBeenUpdated(new ChannelLocalesHaveBeenUpdated('tablet', ['de_DE', 'en_US', 'fr_FR'], ['en_US']));

        $rawParameters = $this->getJobParameters($jobInstance);
        $this->assertSame(['en_US'], array_values($rawParameters['filters']['structure']['locales']));
    }

    private function createJobInstanceWithLocaleFilter(string $jobCode, string $scope, array $locales)
    {
        $entityManager = $this->getFromTestContainer('doctrine.orm.default_entity_manager');
        $jobInstance = new JobInstance('connector', 'type', 'job_name');
        $jobInstance->setCode($jobCode);
        $jobInstance->setLabel($jobCode);
        $jobInstance->setRawParameters([
            'filters' => [
                'structure' => [
                    'scope' => $scope,
                    'locales' => $locales,
                ],
            ],
        ]);
        $entityManager->persist($jobInstance);
        $entityManager->flush();

        return $jobInstance;
    }

    private function getJobParameters(JobInstance $jobInstance): array
    {
        $sql = <<<SQL
SELECT raw_parameters
FROM akeneo_pim.akeneo_batch_job_instance
WHERE id = :jobId
SQL;
        $stmt = $this->getFromTestContainer('doctrine.orm.entity_manager')->getConnection()->prepare($sql);
        $stmt->bindValue('jobId', $jobInstance->getId());
        $stmt->execute();
        $rawParameters = unserialize($stmt->fetchColumn(0));

        return $rawParameters;
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
