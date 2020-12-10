<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Family;

use Akeneo\Pim\Structure\Bundle\Manager\AttributeCodeBlacklister;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\BatchBundle\Job\DoctrineJobRepository;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Component\Batch\Job\JobParameters;

final class AttributeCodeBlacklisterIntegration extends TestCase
{
    public function test_it_blacklists_an_attribute_code(): void
    {
        $blacklister = $this->getBlacklister();
        $this->assertFalse($this->isBlacklisted('nice_attribute_code'));

        $blacklister->blacklist('nice_attribute_code');

        $this->assertTrue($this->isBlacklisted('nice_attribute_code'));
    }

    public function test_it_registers_a_job(): void
    {
        $blacklister = $this->getBlacklister();

        $jobInstance = $this->getJobInstanceRepository()->findOneByIdentifier('clean_removed_attribute_job');
        $jobExecution = $this->getJobExecutionRepository()->createJobExecution(
            $jobInstance,
            new JobParameters(['attribute_code' => 'nice_attribute_code'])
        );

        $blacklister->registerJob('nice_attribute_code', $jobExecution->getId());

        $this->assertJobIsRegistered('nice_attribute_code', $jobExecution->getId());
    }

    public function test_it_whitelists_an_attribute_code(): void
    {
        $blacklister = $this->getBlacklister();

        $blacklister->blacklist('nice_attribute_code');
        $blacklister->removeFromBlacklist('nice_attribute_code');

        $this->assertFalse($this->isBlacklisted('nice_attribute_code'));
    }

    private function isBlacklisted(string $attributeCode): bool
    {
        $query = $this->get('akeneo.pim.structure.query.is_attribute_code_blacklisted');

        return $query->execute($attributeCode);
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
}
