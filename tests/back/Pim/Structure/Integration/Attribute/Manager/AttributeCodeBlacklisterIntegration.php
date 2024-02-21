<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Attribute\Manager;

use Akeneo\Pim\Structure\Bundle\Manager\AttributeCodeBlacklister;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\BatchBundle\Job\DoctrineJobRepository;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;

final class AttributeCodeBlacklisterIntegration extends TestCase
{
    public function test_it_blacklists_attribute_codes(): void
    {
        $blacklister = $this->getBlacklister();

        $attributeCodes = [
            'nice_attribute_code',
            'another_one',
            'a_text',
        ];

        $this->assertFalse($this->areAllBlacklisted($attributeCodes));

        $blacklister->blacklist($attributeCodes);

        $this->assertTrue($this->areAllBlacklisted($attributeCodes));
    }

    public function test_it_registers_a_job(): void
    {
        $blacklister = $this->getBlacklister();

        $jobInstanceCode = 'clean_removed_attribute_job';
        $jobInstance = $this->getJobInstanceRepository()->findOneByIdentifier($jobInstanceCode);
        $job = $this->getJobRegistry()->get($jobInstanceCode);
        $jobExecution = $this->getJobExecutionRepository()->createJobExecution(
            $job,
            $jobInstance,
            new JobParameters(['attribute_code' => 'nice_attribute_code'])
        );

        $blacklister->registerJob(['nice_attribute_code'], $jobExecution->getId());

        $this->assertJobIsRegistered('nice_attribute_code', $jobExecution->getId());
    }

    public function test_it_whitelists_attribute_codes(): void
    {
        $blacklister = $this->getBlacklister();

        $attributeCodes = [
            'nice_attribute_code',
            'another_one',
            'a_text',
        ];

        $blacklister->blacklist($attributeCodes);
        $blacklister->removeFromBlacklist($attributeCodes);

        $this->assertFalse($this->areAllBlacklisted($attributeCodes));
    }

    private function areAllBlacklisted(array $attributeCodes): bool
    {
        $blacklistedAttributeCodes = $this->get('akeneo.pim.structure.query.get_all_blacklisted_attribute_codes')->execute();

        return [] === array_diff($attributeCodes, $blacklistedAttributeCodes);
    }

    private function assertJobIsRegistered(string $attributeCode)
    {
        $query = $this->get('akeneo.pim.structure.query.get_blacklisted_attribute_job_execution_id');
        $result = $query->forAttributeCode($attributeCode);

        $this->assertTrue(false !== $result);
    }

    private function getBlacklister(): AttributeCodeBlacklister
    {
        return $this->get('pim_catalog.manager.attribute_code_blacklister');
    }

    private function getJobExecutionRepository(): DoctrineJobRepository
    {
        return $this->get('akeneo_batch.job_repository');
    }

    private function getJobInstanceRepository(): JobInstanceRepository
    {
        return $this->get('akeneo_batch.job.job_instance_repository');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getJobRegistry(): JobRegistry
    {
        return $this->get('akeneo_batch.job.job_registry');
    }
}
