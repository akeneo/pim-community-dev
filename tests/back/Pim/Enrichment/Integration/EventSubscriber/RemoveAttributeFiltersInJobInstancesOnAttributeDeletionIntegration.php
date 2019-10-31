<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Channel\Integration\Channel\EventListener;

use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Symfony\Component\EventDispatcher\GenericEvent;

class RemoveAttributeFiltersInJobInstancesOnAttributeDeletionIntegration extends TestCase
{
    public function testUpdateExportsFiltersOnAttributeDeletion()
    {
        $jobInstance = $this->createJobInstanceWithAttributeFilter('job1', ['a_yes_no', 'a_text', 'a_date']);
        $jobInstance2 = $this->createJobInstanceWithAttributeFilter('job2', ['a_number', 'a_date', 'a_text']);

        $this
            ->get('pim_enrich.event_listener.remove_attribute_filter_in_job_instances')
            ->removeDeletedAttributeFromJobInstancesFilters(new GenericEvent((new Attribute())->setCode('a_text')));

        $rawParameters = $this->getJobParameters($jobInstance);
        $this->assertSame(['a_yes_no', 'a_date'], $rawParameters['filters']['structure']['attributes']);

        $rawParameters = $this->getJobParameters($jobInstance2);
        $this->assertSame(['a_number', 'a_date'], $rawParameters['filters']['structure']['attributes']);
    }

    private function createJobInstanceWithAttributeFilter(string $jobCode, array $attributes)
    {
        $entityManager = $this->get('doctrine.orm.default_entity_manager');
        $jobInstance = new JobInstance('connector', JobInstance::TYPE_EXPORT, 'job_name');
        $jobInstance->setCode($jobCode);
        $jobInstance->setLabel($jobCode);
        $jobInstance->setRawParameters([
            'filters' => [
                'structure' => [
                    'attributes' => $attributes,
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
FROM akeneo_batch_job_instance
WHERE id = :jobId
SQL;
        $stmt = $this->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql);
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
