<?php

namespace Akeneo\Tool\Bundle\DatabaseMetadataBundle\tests\integration;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Services\EntityContextProvider;
use PHPUnit\Framework\Assert;

class EntityContextProviderIntegration extends  TestCase
{

    private EntityContextProvider $contextProvider;
    private JobInstanceRepository $jobInstanceRepository;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->contextProvider = $this->get('akeneo_tool_bundle_database_metadata.services.entity_context_provider');
        $this->jobInstanceRepository = $this->get('akeneo_batch.job.job_instance_repository');
    }

    public function  test_it_can_produce_context_on_job_instance_entity() {
        $jobInstance=$this->jobInstanceRepository->findOneByIdentifier("add_product_value");
        Assert::assertEquals(['job_instance'=>['id' => $jobInstance->getId()]], $this->contextProvider->mapEntity2LogContext($jobInstance));
    }


    public function  test_it_can_not_produce_context_on_job_instance_entity() {
        $object = $this;
        Assert::assertNull($this->contextProvider->mapEntity2LogContext($object));
    }

}
